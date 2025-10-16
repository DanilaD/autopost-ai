<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\UserManagementService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for managing users (admin only).
 *
 * Handles user listing, suspension, password resets, and statistics.
 * All operations require admin privileges.
 */
class UserManagementController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected UserManagementService $userManagementService,
        protected UserService $userService
    ) {}

    /**
     * Display paginated list of users.
     */
    public function index(Request $request): Response
    {
        $currentCompany = $request->user()->currentCompany;

        // Get users with filters, including role information
        $users = $this->userManagementService->getUsers([
            'search' => $request->input('search'),
            'sort' => $request->input('sort', 'created_at'),
            'direction' => $request->input('direction', 'desc'),
            'status' => $request->input('status'),
            'per_page' => 15,
        ]);

        // Transform users to include their role in current company
        $users->getCollection()->transform(function ($user) use ($currentCompany) {
            // Get role in current company
            $roleInCurrentCompany = null;
            if ($currentCompany) {
                $role = $this->userService->getRoleIn($user, $currentCompany);
                $roleInCurrentCompany = $role ? $role->value : null;
            }

            return array_merge($user->toArray(), [
                'role_in_current_company' => $roleInCurrentCompany,
                'is_suspended' => $user->isSuspended(),
                'stats' => [
                    'companies_count' => $user->companies_count,
                    'instagram_accounts_count' => $user->owned_instagram_accounts_count,
                    'posts_count' => $user->instagram_posts_count,
                ],
            ]);
        });

        // Get management statistics
        $stats = $this->userManagementService->getUserManagementStats();

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->input('search'),
                'sort' => $request->input('sort', 'created_at'),
                'direction' => $request->input('direction', 'desc'),
                'status' => $request->input('status'),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Send password reset link to user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendPasswordReset(Request $request, int $id)
    {
        try {
            $message = $this->userManagementService->sendPasswordResetLink($id);

            return back()->with('toast', [
                'message' => $message,
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'message' => 'Failed to send password reset link: '.$e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    /**
     * Suspend a user account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function suspend(Request $request, int $id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        try {
            $this->userManagementService->suspendUser(
                $id,
                $request->input('reason'),
                $request->user()
            );

            return back()->with('toast', [
                'message' => 'User suspended successfully.',
                'type' => 'success',
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->with('toast', [
                'message' => $e->getMessage(),
                'type' => 'error',
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'message' => 'Failed to suspend user: '.$e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    /**
     * Unsuspend a user account.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function unsuspend(Request $request, int $id)
    {
        try {
            $this->userManagementService->unsuspendUser($id);

            return back()->with('toast', [
                'message' => 'User access restored successfully.',
                'type' => 'success',
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->with('toast', [
                'message' => $e->getMessage(),
                'type' => 'error',
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'message' => 'Failed to restore user access: '.$e->getMessage(),
                'type' => 'error',
            ]);
        }
    }
}
