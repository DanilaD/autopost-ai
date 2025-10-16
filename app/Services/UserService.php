<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\InstagramAccount;
use App\Models\User;

/**
 * User Service
 *
 * Handles all business logic related to user management.
 * Extracted from User model to follow the "no updates, only relationships" pattern.
 */
class UserService
{
    /**
     * Switch to a different company
     */
    public function switchCompany(User $user, Company $company): bool
    {
        if (! $user->companies->contains($company)) {
            return false;
        }

        $user->update(['current_company_id' => $company->id]);

        return true;
    }

    /**
     * Suspend this user account.
     *
     * @param  string  $reason  Reason for suspension
     * @param  User  $suspendedBy  Admin user performing the suspension
     */
    public function suspend(User $user, string $reason, User $suspendedBy): bool
    {
        return $user->update([
            'suspended_at' => now(),
            'suspended_by' => $suspendedBy->id,
            'suspension_reason' => $reason,
        ]);
    }

    /**
     * Unsuspend this user account.
     */
    public function unsuspend(User $user): bool
    {
        return $user->update([
            'suspended_at' => null,
            'suspended_by' => null,
            'suspension_reason' => null,
        ]);
    }

    /**
     * Check if user has a specific role in a company
     */
    public function hasRole(User $user, Company $company, UserRole $role): bool
    {
        return $user->companies()
            ->where('company_id', $company->id)
            ->wherePivot('role', $role->value)
            ->exists();
    }

    /**
     * Check if user is admin in a company
     */
    public function isAdmin(User $user, Company $company): bool
    {
        return $user->companies()
            ->where('company_id', $company->id)
            ->wherePivotIn('role', [UserRole::ADMIN->value, UserRole::NETWORK->value])
            ->exists();
    }

    /**
     * Check if user is admin in current company
     */
    public function isAdminInCurrentCompany(User $user): bool
    {
        if (! $user->current_company_id) {
            return false;
        }

        return $user->companies()
            ->where('company_id', $user->current_company_id)
            ->wherePivot('role', UserRole::ADMIN->value)
            ->exists();
    }

    /**
     * Get user's role in a specific company
     */
    public function getRoleIn(User $user, Company $company): ?UserRole
    {
        $pivot = $user->companies()
            ->where('company_id', $company->id)
            ->first()?->pivot;

        return $pivot ? UserRole::from($pivot->role) : null;
    }

    /**
     * Check if user has any Instagram accounts.
     */
    public function hasInstagramAccounts(User $user): bool
    {
        return $user->ownedInstagramAccounts()->exists()
            || $user->sharedInstagramAccounts()->exists()
            || $user->currentCompany?->instagramAccounts()->exists();
    }

    /**
     * Get the default Instagram account for this user.
     * Priority: User's first active account > Company's first active account > Any account
     */
    public function getDefaultInstagramAccount(User $user): ?InstagramAccount
    {
        // Try user's own active accounts first
        $account = $user->ownedInstagramAccounts()
            ->where('status', 'active')
            ->first();

        if ($account) {
            return $account;
        }

        // Try company's active accounts
        if ($user->currentCompany) {
            $account = $user->currentCompany->instagramAccounts()
                ->where('status', 'active')
                ->first();

            if ($account) {
                return $account;
            }
        }

        // Fall back to any accessible account
        return $user->accessibleInstagramAccounts()->first();
    }

    /**
     * Get user statistics for admin panel.
     */
    public function getStats(User $user, ?string $roleInCurrentCompany = null): array
    {
        return [
            'companies_count' => $user->companies()->count(),
            'instagram_accounts_count' => $user->accessibleInstagramAccounts()->count(),
            'posts_count' => $user->instagramPosts()->count(),
            'last_login' => $user->last_login_at?->diffForHumans(),
            'account_age_days' => $user->created_at->diffInDays(now()),
            'role_in_current_company' => $roleInCurrentCompany,
        ];
    }
}
