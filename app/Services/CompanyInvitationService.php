<?php

namespace App\Services;

use App\Mail\CompanyInvitationMail;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CompanyInvitationService
{
    protected CompanyService $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * Send an invitation to join a company
     */
    public function sendInvitation(Company $company, string $email, string $role, User $inviter, ?string $locale = null): CompanyInvitation
    {
        // Check if user is already a member
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $this->companyService->hasMember($company, $existingUser)) {
            throw new \Exception('User is already a member of this company.');
        }

        // Check if there's already a pending invitation
        $existingInvitation = CompanyInvitation::where('company_id', $company->id)
            ->where('email', $email)
            ->pending()
            ->first();

        if ($existingInvitation) {
            throw new \Exception('An invitation has already been sent to this email address.');
        }

        // Create the invitation
        $invitation = CompanyInvitation::create([
            'company_id' => $company->id,
            'email' => $email,
            'role' => $role,
            'invited_by' => $inviter->id,
        ]);

        // Send email invitation immediately (not queued)
        $localeToUse = $locale ?: app()->getLocale();
        Mail::to($email)->send((new CompanyInvitationMail($invitation))->locale($localeToUse));

        return $invitation;
    }

    /**
     * Accept an invitation
     */
    public function acceptInvitation(string $token, User $user): bool
    {
        $invitation = CompanyInvitation::where('token', $token)->first();

        if (! $invitation) {
            throw new \Exception('Invalid invitation token.');
        }

        if ($invitation->isExpired()) {
            throw new \Exception('This invitation has expired.');
        }

        if ($invitation->isAccepted()) {
            throw new \Exception('This invitation has already been accepted.');
        }

        if ($invitation->email !== $user->email) {
            throw new \Exception('This invitation is not for your email address.');
        }

        return DB::transaction(function () use ($invitation, $user) {
            // Add user to company
            $invitation->company->users()->attach($user->id, [
                'role' => $invitation->role,
                'accepted_at' => now(),
            ]);

            // Mark invitation as accepted
            $invitation->update(['accepted_at' => now()]);

            // If user doesn't have a current company, set this one
            if (! $user->currentCompany) {
                $user->update(['current_company_id' => $invitation->company->id]);
            }

            return true;
        });
    }

    /**
     * Cancel an invitation
     */
    public function cancelInvitation(CompanyInvitation $invitation, User $canceler): bool
    {
        // Check if user has permission to cancel invitations
        if (! $this->canManageInvitations($invitation->company, $canceler)) {
            throw new \Exception('You do not have permission to cancel this invitation.');
        }

        if ($invitation->isAccepted()) {
            throw new \Exception('Cannot cancel an accepted invitation.');
        }

        return $invitation->delete();
    }

    /**
     * Update user role in company
     */
    public function updateUserRole(Company $company, User $targetUser, string $newRole, User $updater): bool
    {
        // Check if updater has permission
        if (! $this->canManageUsers($company, $updater)) {
            throw new \Exception('You do not have permission to update user roles.');
        }

        // Check if target user is a member
        if (! $this->companyService->hasMember($company, $targetUser)) {
            throw new \Exception('User is not a member of this company.');
        }

        // Prevent non-owners from making others admin
        $updaterRole = $this->companyService->getUserRole($company, $updater);
        if ($newRole === 'admin' && $updaterRole !== 'admin') {
            throw new \Exception('Only company admins can promote users to admin.');
        }

        return $company->users()->updateExistingPivot($targetUser->id, [
            'role' => $newRole,
        ]);
    }

    /**
     * Remove user from company
     */
    public function removeUser(Company $company, User $targetUser, User $remover): bool
    {
        // Check if remover has permission
        if (! $this->canManageUsers($company, $remover)) {
            throw new \Exception('You do not have permission to remove users.');
        }

        // Prevent removing the company owner
        if ($company->owner_id === $targetUser->id) {
            throw new \Exception('Cannot remove the company owner.');
        }

        // Prevent non-admins from removing admins
        $removerRole = $this->companyService->getUserRole($company, $remover);
        $targetRole = $this->companyService->getUserRole($company, $targetUser);
        if ($targetRole === 'admin' && $removerRole !== 'admin') {
            throw new \Exception('Only company admins can remove other admins.');
        }

        return $company->users()->detach($targetUser->id);
    }

    /**
     * Check if user can manage invitations
     */
    public function canManageInvitations(Company $company, User $user): bool
    {
        $role = $this->companyService->getUserRole($company, $user);

        return in_array($role, ['admin', 'network']);
    }

    /**
     * Check if user can manage other users
     */
    public function canManageUsers(Company $company, User $user): bool
    {
        $role = $this->companyService->getUserRole($company, $user);

        return in_array($role, ['admin', 'network']);
    }

    /**
     * Get company invitations with pagination
     */
    public function getCompanyInvitations(Company $company, array $filters = [])
    {
        $query = $company->invitations()->with(['inviter']);

        if (isset($filters['status'])) {
            switch ($filters['status']) {
                case 'pending':
                    $query->pending();
                    break;
                case 'accepted':
                    $query->accepted();
                    break;
                case 'expired':
                    $query->expired();
                    break;
            }
        }

        if (isset($filters['search'])) {
            $query->where('email', 'like', '%'.$filters['search'].'%');
        }

        return $query->orderBy('invited_at', 'desc')->paginate(15);
    }
}
