<?php

namespace App\Services;

use App\Models\InstagramAccount;
use App\Models\User;

/**
 * Instagram Account Service
 *
 * Handles all business logic related to Instagram account management.
 * Extracted from InstagramAccount model to follow the "no updates, only relationships" pattern.
 */
class InstagramAccountService
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Mark the account as expired.
     */
    public function markAsExpired(InstagramAccount $account): void
    {
        $account->update(['status' => 'expired']);
    }

    /**
     * Mark the account as disconnected.
     */
    public function markAsDisconnected(InstagramAccount $account): void
    {
        $account->update(['status' => 'disconnected']);
    }

    /**
     * Mark the account as active.
     */
    public function markAsActive(InstagramAccount $account): void
    {
        $account->update(['status' => 'active']);
    }

    /**
     * Update the last synced timestamp.
     */
    public function touchLastSynced(InstagramAccount $account): void
    {
        $account->update(['last_synced_at' => now()]);
    }

    /**
     * Share this account with a user.
     */
    public function shareWith(InstagramAccount $account, User $user, bool $canPost = true, bool $canManage = false, ?User $sharedBy = null): void
    {
        $account->sharedWithUsers()->syncWithoutDetaching([
            $user->id => [
                'can_post' => $canPost,
                'can_manage' => $canManage,
                'shared_by_user_id' => $sharedBy?->id,
                'shared_at' => now(),
            ],
        ]);

        // Mark account as shared
        if (! $account->is_shared) {
            $account->update(['is_shared' => true]);
        }
    }

    /**
     * Revoke access for a user.
     */
    public function revokeAccessFor(InstagramAccount $account, User $user): void
    {
        $account->sharedWithUsers()->detach($user->id);

        // Update is_shared flag if no one has access anymore
        if ($account->sharedWithUsers()->count() === 0) {
            $account->update(['is_shared' => false]);
        }
    }

    /**
     * Check if a specific user owns this account.
     */
    public function isOwnedBy(InstagramAccount $account, User $user): bool
    {
        return $account->user_id === $user->id;
    }

    /**
     * Check if a specific user has access to this account.
     */
    public function isAccessibleBy(InstagramAccount $account, User $user): bool
    {
        // Owner has access
        if ($this->isOwnedBy($account, $user)) {
            return true;
        }

        // Check if user is member of the company that owns this account
        if ($account->company_id && $this->companyService->hasMember($account->company, $user)) {
            return true;
        }

        // Check if account is shared with this user
        return $account->sharedWithUsers()->where('users.id', $user->id)->exists();
    }

    /**
     * Check if a user can post to this account.
     */
    public function canUserPost(InstagramAccount $account, User $user): bool
    {
        // Owner can always post
        if ($this->isOwnedBy($account, $user)) {
            return true;
        }

        // Check company membership (admins and members can post)
        if ($account->company_id && $this->companyService->hasMember($account->company, $user)) {
            return true;
        }

        // Check shared permissions
        $sharedUser = $account->sharedWithUsers()
            ->where('users.id', $user->id)
            ->first();

        return $sharedUser?->pivot?->can_post ?? false;
    }

    /**
     * Check if a user can manage this account (settings, reconnect, disconnect).
     */
    public function canUserManage(InstagramAccount $account, User $user): bool
    {
        // Owner can always manage
        if ($this->isOwnedBy($account, $user)) {
            return true;
        }

        // Check if user is admin or network in the company that owns this account
        if ($account->company_id && $this->companyService->hasMember($account->company, $user)) {
            $userRole = $this->companyService->getUserRole($account->company, $user);
            if (in_array($userRole, ['admin', 'network'])) {
                return true;
            }
        }

        // Check shared permissions
        $sharedUser = $account->sharedWithUsers()
            ->where('users.id', $user->id)
            ->first();

        return $sharedUser?->pivot?->can_manage ?? false;
    }
}
