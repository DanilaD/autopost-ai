<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Password;

/**
 * Service for managing user operations.
 *
 * Handles user listing, suspension, password resets, and statistics.
 * All business logic for user management is centralized here.
 */
class UserManagementService
{
    /**
     * Get paginated list of users with optional filtering and sorting.
     *
     * @param array{
     *     search?: string,
     *     sort?: string,
     *     direction?: string,
     *     per_page?: int,
     *     company_id?: int,
     *     status?: string
     * } $filters
     */
    public function getUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::query()
            ->with(['currentCompany', 'companies'])
            ->withCount(['companies', 'ownedInstagramAccounts', 'instagramPosts']);

        // Apply search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%');
            });
        }

        // Filter by status
        if (! empty($filters['status'])) {
            if ($filters['status'] === 'suspended') {
                $query->whereNotNull('suspended_at');
            } elseif ($filters['status'] === 'active') {
                $query->whereNull('suspended_at');
            }
        }

        // Filter by company
        if (! empty($filters['company_id'])) {
            $query->whereHas('companies', function ($q) use ($filters) {
                $q->where('company_id', $filters['company_id']);
            });
        }

        // Apply sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        // Validate sort field
        $allowedSortFields = ['name', 'email', 'created_at', 'last_login_at', 'suspended_at'];
        if (! in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        // Validate sort direction
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';

        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Send password reset link to a user.
     *
     * @return string Status message
     */
    public function sendPasswordResetLink(int $userId): string
    {
        $user = User::findOrFail($userId);

        // Send the password reset notification
        $status = Password::sendResetLink(['email' => $user->email]);

        return $status === Password::RESET_LINK_SENT
            ? 'Password reset link sent successfully.'
            : 'Unable to send password reset link.';
    }

    /**
     * Suspend a user account.
     */
    public function suspendUser(int $userId, string $reason, User $suspendedBy): bool
    {
        $user = User::findOrFail($userId);

        // Don't allow suspending yourself
        if ($user->id === $suspendedBy->id) {
            throw new \InvalidArgumentException('You cannot suspend yourself.');
        }

        // Don't allow suspending if already suspended
        if ($user->isSuspended()) {
            throw new \InvalidArgumentException('User is already suspended.');
        }

        return $user->suspend($reason, $suspendedBy);
    }

    /**
     * Unsuspend a user account.
     */
    public function unsuspendUser(int $userId): bool
    {
        $user = User::findOrFail($userId);

        if (! $user->isSuspended()) {
            throw new \InvalidArgumentException('User is not suspended.');
        }

        return $user->unsuspend();
    }

    /**
     * Get user statistics.
     *
     * @return array{
     *     instagram_accounts: int,
     *     posts_count: int,
     *     companies_count: int,
     *     last_login: ?string,
     *     account_age_days: int,
     *     role_in_current_company: ?string
     * }
     */
    public function getUserStats(int $userId): array
    {
        $user = User::with(['companies', 'ownedInstagramAccounts', 'instagramPosts'])
            ->findOrFail($userId);

        // Get role in current company
        $roleInCurrentCompany = null;
        if ($user->current_company_id) {
            $company = Company::find($user->current_company_id);
            if ($company) {
                $role = $user->getRoleIn($company);
                $roleInCurrentCompany = $role ? $role->value : null;
            }
        }

        return [
            'instagram_accounts' => $user->accessibleInstagramAccounts()->count(),
            'posts_count' => $user->instagramPosts()->count(),
            'companies_count' => $user->companies()->count(),
            'last_login' => $user->last_login_at?->format('Y-m-d H:i:s'),
            'account_age_days' => $user->created_at->diffInDays(now()),
            'role_in_current_company' => $roleInCurrentCompany,
        ];
    }

    /**
     * Search users by name or email.
     */
    public function searchUsers(string $searchTerm, int $perPage = 15): LengthAwarePaginator
    {
        return User::where('name', 'like', '%'.$searchTerm.'%')
            ->orWhere('email', 'like', '%'.$searchTerm.'%')
            ->with(['currentCompany', 'companies'])
            ->withCount(['companies', 'ownedInstagramAccounts', 'instagramPosts'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get users for a specific company with their roles.
     */
    public function getUsersForCompany(int $companyId, int $perPage = 15): LengthAwarePaginator
    {
        return User::whereHas('companies', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with(['companies' => function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            }])
            ->withCount(['companies', 'ownedInstagramAccounts', 'instagramPosts'])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Update user's last login timestamp.
     */
    public function updateLastLogin(int $userId): bool
    {
        return User::where('id', $userId)->update([
            'last_login_at' => now(),
        ]);
    }

    /**
     * Get user management statistics.
     *
     * @return array{
     *     total_users: int,
     *     active_users: int,
     *     suspended_users: int,
     *     new_this_month: int
     * }
     */
    public function getUserManagementStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::whereNull('suspended_at')->count(),
            'suspended_users' => User::whereNotNull('suspended_at')->count(),
            'new_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }
}
