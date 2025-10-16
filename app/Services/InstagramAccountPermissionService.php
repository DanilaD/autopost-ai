<?php

namespace App\Services;

use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Instagram Account Permission Service
 *
 * Centralizes all permission checking logic for Instagram accounts.
 * Handles ownership, sharing, and company-level access control.
 */
class InstagramAccountPermissionService
{
    protected InstagramAccountService $accountService;

    protected CompanyService $companyService;

    public function __construct(InstagramAccountService $accountService, CompanyService $companyService)
    {
        $this->accountService = $accountService;
        $this->companyService = $companyService;
    }

    /**
     * Check if a user can view an Instagram account.
     *
     * View permissions are granted if:
     * - User owns the account
     * - User is a member of the company that owns the account
     * - Account is shared with the user
     */
    public function canView(User $user, InstagramAccount $account): bool
    {
        return $this->accountService->isAccessibleBy($account, $user);
    }

    /**
     * Check if a user can post to an Instagram account.
     *
     * Post permissions are granted if:
     * - User owns the account
     * - User is a member of the company that owns the account
     * - Account is shared with the user with can_post = true
     */
    public function canPost(User $user, InstagramAccount $account): bool
    {
        return $this->accountService->canUserPost($account, $user);
    }

    /**
     * Check if a user can manage an Instagram account.
     *
     * Management permissions (reconnect, disconnect, settings) are granted if:
     * - User owns the account
     * - User is an admin in the company that owns the account
     * - Account is shared with the user with can_manage = true
     */
    public function canManage(User $user, InstagramAccount $account): bool
    {
        return $this->accountService->canUserManage($account, $user);
    }

    /**
     * Check if a user can share an Instagram account with others.
     *
     * Sharing permissions are granted if:
     * - User owns the account (can share personal accounts)
     * - User is an admin in the company that owns the account
     */
    public function canShare(User $user, InstagramAccount $account): bool
    {
        // Owner can share
        if ($this->accountService->isOwnedBy($account, $user)) {
            return true;
        }

        // Company admin can share company accounts
        if ($account->company_id && $account->company) {
            return $this->companyService->getUserRole($account->company, $user) === 'admin';
        }

        return false;
    }

    /**
     * Check if a user can revoke access for another user.
     */
    public function canRevokeAccess(User $user, InstagramAccount $account, User $targetUser): bool
    {
        // Can't revoke your own access
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Must have sharing permissions
        return $this->canShare($user, $account);
    }

    /**
     * Check if a user can delete/disconnect an Instagram account.
     */
    public function canDelete(User $user, InstagramAccount $account): bool
    {
        // Owner can always delete
        if ($this->accountService->isOwnedBy($account, $user)) {
            return true;
        }

        // Company admin can delete company accounts
        if ($account->company_id && $account->company) {
            return $this->companyService->getUserRole($account->company, $user) === 'admin';
        }

        return false;
    }

    /**
     * Get all Instagram accounts accessible by a user with their permission levels.
     *
     * @return array Array of accounts with permission flags
     */
    public function getAccessibleAccountsWithPermissions(User $user): array
    {
        $accounts = InstagramAccount::accessibleBy($user)
            ->with(['owner', 'company', 'sharedWithUsers'])
            ->get();

        return $accounts->map(function ($account) use ($user) {
            return [
                'account' => $account,
                'permissions' => [
                    'can_view' => true, // Always true since we only fetched accessible accounts
                    'can_post' => $this->canPost($user, $account),
                    'can_manage' => $this->canManage($user, $account),
                    'can_share' => $this->canShare($user, $account),
                    'can_delete' => $this->canDelete($user, $account),
                ],
                'access_type' => $this->getAccessType($user, $account),
            ];
        })->all();
    }

    /**
     * Determine how a user has access to an account.
     *
     * @return string 'owner', 'company', or 'shared'
     */
    public function getAccessType(User $user, InstagramAccount $account): string
    {
        if ($this->accountService->isOwnedBy($account, $user)) {
            return 'owner';
        }

        if ($account->company_id && $this->companyService->hasMember($account->company, $user)) {
            return 'company';
        }

        if ($account->sharedWithUsers()->where('users.id', $user->id)->exists()) {
            return 'shared';
        }

        return 'none';
    }

    /**
     * Share an account with a user.
     */
    public function shareAccount(
        InstagramAccount $account,
        User $targetUser,
        User $sharingUser,
        bool $canPost = true,
        bool $canManage = false
    ): bool {
        // Check if sharing user has permission to share
        if (! $this->canShare($sharingUser, $account)) {
            Log::warning('Unauthorized sharing attempt', [
                'account_id' => $account->id,
                'sharing_user_id' => $sharingUser->id,
                'target_user_id' => $targetUser->id,
            ]);

            return false;
        }

        // Can't share with yourself
        if ($sharingUser->id === $targetUser->id) {
            return false;
        }

        // Can't share with account owner
        if ($this->accountService->isOwnedBy($account, $targetUser)) {
            return false;
        }

        try {
            $this->accountService->shareWith($account, $targetUser, $canPost, $canManage, $sharingUser);

            Log::info('Instagram account shared', [
                'account_id' => $account->id,
                'shared_with_user_id' => $targetUser->id,
                'shared_by_user_id' => $sharingUser->id,
                'can_post' => $canPost,
                'can_manage' => $canManage,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to share Instagram account', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Revoke a user's access to an account.
     */
    public function revokeAccess(
        InstagramAccount $account,
        User $targetUser,
        User $revokingUser
    ): bool {
        // Check if revoking user has permission
        if (! $this->canRevokeAccess($revokingUser, $account, $targetUser)) {
            Log::warning('Unauthorized access revocation attempt', [
                'account_id' => $account->id,
                'revoking_user_id' => $revokingUser->id,
                'target_user_id' => $targetUser->id,
            ]);

            return false;
        }

        try {
            $this->accountService->revokeAccessFor($account, $targetUser);

            Log::info('Instagram account access revoked', [
                'account_id' => $account->id,
                'revoked_from_user_id' => $targetUser->id,
                'revoked_by_user_id' => $revokingUser->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to revoke Instagram account access', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Validate if a user can perform an action, throw exception if not.
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorize(User $user, InstagramAccount $account, string $action): void
    {
        $canPerformAction = match ($action) {
            'view' => $this->canView($user, $account),
            'post' => $this->canPost($user, $account),
            'manage' => $this->canManage($user, $account),
            'share' => $this->canShare($user, $account),
            'delete' => $this->canDelete($user, $account),
            default => false,
        };

        if (! $canPerformAction) {
            throw new \Illuminate\Auth\Access\AuthorizationException(
                "Unauthorized to {$action} this Instagram account."
            );
        }
    }
}
