<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

/**
 * Service for managing user impersonation.
 *
 * Handles starting and stopping impersonation sessions.
 * Provides security checks and audit logging.
 */
class ImpersonationService
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Start impersonating a user.
     *
     * @param  User  $admin  The admin user starting impersonation
     * @param  int  $targetUserId  The user to impersonate
     */
    public function impersonate(User $admin, int $targetUserId): bool
    {
        $targetUser = User::findOrFail($targetUserId);

        // Security checks
        if (! $this->canImpersonate($admin, $targetUser)) {
            throw new \InvalidArgumentException('You do not have permission to impersonate this user.');
        }

        // Store original admin information in session
        Session::put('impersonate', [
            'admin_id' => $admin->id,
            'admin_name' => $admin->name,
            'admin_email' => $admin->email,
            'started_at' => now()->toDateTimeString(),
            'target_user_id' => $targetUser->id,
        ]);

        // Log the impersonation start
        $this->logImpersonation($admin->id, $targetUser->id, 'started');

        // Switch to target user
        Auth::login($targetUser);

        return true;
    }

    /**
     * Stop impersonating and return to original admin account.
     */
    public function stopImpersonation(): bool
    {
        $impersonateData = Session::get('impersonate');

        if (! $impersonateData) {
            throw new \InvalidArgumentException('No active impersonation session.');
        }

        $adminId = $impersonateData['admin_id'];
        $targetUserId = $impersonateData['target_user_id'];

        // Log the impersonation end
        $this->logImpersonation($adminId, $targetUserId, 'stopped');

        // Remove impersonation data from session
        Session::forget('impersonate');

        // Switch back to admin user
        $admin = User::findOrFail($adminId);
        Auth::login($admin);

        return true;
    }

    /**
     * Check if a user can impersonate another user.
     */
    public function canImpersonate(User $admin, User $target): bool
    {
        // Cannot impersonate yourself
        if ($admin->id === $target->id) {
            return false;
        }

        // Admin must have admin role in their current company
        if (! $this->userService->isAdminInCurrentCompany($admin)) {
            return false;
        }

        // Cannot impersonate another admin (prevent privilege escalation)
        // Check if target is admin in any company
        $targetIsAdmin = $target->companies()
            ->wherePivot('role', 'admin')
            ->exists();

        if ($targetIsAdmin) {
            return false;
        }

        // Cannot impersonate suspended users
        if ($target->isSuspended()) {
            return false;
        }

        return true;
    }

    /**
     * Check if currently impersonating.
     */
    public function isImpersonating(): bool
    {
        return Session::has('impersonate');
    }

    /**
     * Get impersonation session data.
     */
    public function getImpersonationData(): ?array
    {
        return Session::get('impersonate');
    }

    /**
     * Get the original admin user during impersonation.
     */
    public function getOriginalAdmin(): ?User
    {
        $data = $this->getImpersonationData();

        if (! $data) {
            return null;
        }

        return User::find($data['admin_id']);
    }

    /**
     * Log impersonation action for audit trail.
     */
    protected function logImpersonation(int $adminId, int $targetUserId, string $action): void
    {
        $admin = User::find($adminId);
        $target = User::find($targetUserId);

        Log::info('User Impersonation', [
            'action' => $action,
            'admin_id' => $adminId,
            'admin_email' => $admin?->email,
            'target_user_id' => $targetUserId,
            'target_email' => $target?->email,
            'timestamp' => now()->toDateTimeString(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get impersonation history from logs (if implementing database logging).
     * This is a placeholder for future database-backed audit logs.
     */
    public function getImpersonationHistory(?int $userId = null): array
    {
        // TODO: Implement database-backed impersonation logs
        // For now, impersonation is logged to Laravel logs
        return [];
    }

    /**
     * Validate impersonation session hasn't expired.
     * Auto-expires after 60 minutes.
     */
    public function validateImpersonationSession(): bool
    {
        $data = $this->getImpersonationData();

        if (! $data) {
            return false;
        }

        $startedAt = \Carbon\Carbon::parse($data['started_at']);
        $expiresAt = $startedAt->addHour();

        // If expired, automatically stop impersonation
        if (now()->greaterThan($expiresAt)) {
            $this->stopImpersonation();

            return false;
        }

        return true;
    }
}
