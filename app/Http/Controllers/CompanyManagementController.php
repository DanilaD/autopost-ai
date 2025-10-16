<?php

namespace App\Http\Controllers;

use App\Mail\CompanyInvitationMail;
use App\Models\Company;
use App\Models\CompanyInvitation;
use App\Models\User;
use App\Services\CompanyInvitationService;
use App\Services\CompanyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class CompanyManagementController extends Controller
{
    public function __construct(
        private CompanyInvitationService $invitationService,
        private CompanyService $companyService
    ) {}

    /**
     * Display company settings and team management
     */
    public function settings(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        // Check if user is authenticated
        if (! $user) {
            return redirect()->route('home');
        }

        $company = $user->currentCompany;

        if (! $company) {
            return redirect()->route('companies.create')->with('toast', [
                'message' => __('company.toast.need_company_first'),
                'type' => 'info',
            ]);
        }

        // Check if user is admin
        if (! $this->invitationService->canManageUsers($company, $user)) {
            abort(403, 'You do not have permission to access company settings.');
        }

        // Get team members with their roles
        $teamMembers = $this->companyService->getTeamMembers($company);

        // Get pending invitations
        $invitations = $this->invitationService->getCompanyInvitations($company, [
            'status' => 'pending',
        ]);

        return Inertia::render('Company/Settings', [
            'company' => $company,
            'teamMembers' => $teamMembers,
            'invitations' => $invitations,
            'canManageUsers' => $this->invitationService->canManageUsers($company, $user),
        ]);
    }

    /**
     * Send invitation to join company
     */
    public function invite(Request $request): RedirectResponse
    {
        return $this->sendInvitation($request);
    }

    /**
     * Send invitation to join company
     */
    public function sendInvitation(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Check if user is authenticated
        if (! $user) {
            return redirect()->route('home');
        }

        $company = $user->currentCompany;

        if (! $company) {
            return back()->with('toast', [
                'message' => __('company.toast.need_company_first'),
                'type' => 'error',
            ]);
        }

        if (! $this->invitationService->canManageInvitations($company, $user)) {
            abort(403, 'You do not have permission to send invitations.');
        }

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'role' => 'required|in:admin,user,network',
        ]);

        try {
            $invitation = $this->invitationService->sendInvitation(
                $company,
                $validated['email'],
                $validated['role'],
                $user
            );

            return back()->with('toast', [
                'message' => __('company.invitation.sent', ['email' => $validated['email']]),
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'message' => $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    /**
     * Cancel an invitation
     */
    public function cancelInvitation(Request $request, CompanyInvitation $invitation): RedirectResponse
    {
        $user = $request->user();

        try {
            $this->invitationService->cancelInvitation($invitation, $user);

            return back()->with('toast', [
                'message' => __('company.invitation.cancelled'),
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'message' => $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    /**
     * Resend an invitation
     */
    public function resendInvitation(Request $request, CompanyInvitation $invitation): RedirectResponse
    {
        $user = $request->user();

        try {
            // Only allow resending for pending invitations
            if ($invitation->isAccepted()) {
                return back()->with('toast', [
                    'message' => __('company.invitation.cannot_resend_accepted'),
                    'type' => 'error',
                ]);
            }

            // Permission check
            if (! $this->invitationService->canManageInvitations($invitation->company, $user)) {
                abort(403, 'You do not have permission to resend invitations.');
            }

            // Determine locale: use current page locale if provided, else app locale
            $locale = $request->input('locale') ?: app()->getLocale();

            // Resend the invitation email (not queued)
            Mail::to($invitation->email)->send((new CompanyInvitationMail($invitation))->locale($locale));

            return back()->with('toast', [
                'message' => __('company.invitation.resent'),
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'message' => $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    /**
     * Update user role
     */
    public function updateUserRole(Request $request, User $targetUser): RedirectResponse
    {
        $user = $request->user();

        // Check if user is authenticated
        if (! $user) {
            return redirect()->route('home');
        }

        $company = $user->currentCompany;

        $validated = $request->validate([
            'role' => 'required|in:admin,user,network',
        ]);

        try {
            $this->invitationService->updateUserRole(
                $company,
                $targetUser,
                $validated['role'],
                $user
            );

            return back()->with('toast', [
                'message' => __('company.toast.role_updated'),
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'message' => $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    /**
     * Remove user from company
     */
    public function removeUser(Request $request, User $targetUser): RedirectResponse
    {
        $user = $request->user();

        // Check if user is authenticated
        if (! $user) {
            return redirect()->route('home');
        }

        $company = $user->currentCompany;

        try {
            $this->invitationService->removeUser($company, $targetUser, $user);

            return back()->with('toast', [
                'message' => __('company.toast.user_removed'),
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'message' => $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    /**
     * Accept invitation (public route)
     */
    public function acceptInvitation(Request $request, string $token): RedirectResponse
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->with('toast', [
                'message' => __('company.toast.login_required'),
                'type' => 'info',
            ]);
        }

        try {
            $this->invitationService->acceptInvitation($token, $user);

            return redirect()->route('dashboard')->with('toast', [
                'message' => __('company.toast.accepted'),
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->with('toast', [
                'message' => $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }
}
