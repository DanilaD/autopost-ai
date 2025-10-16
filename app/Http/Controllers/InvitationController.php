<?php

namespace App\Http\Controllers;

use App\Models\CompanyInvitation;
use App\Services\CompanyInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class InvitationController extends Controller
{
    /**
     * Constructor dependency injection
     */
    public function __construct(
        private CompanyInvitationService $invitationService
    ) {}

    /**
     * Show invitation details and handle acceptance flow
     */
    public function show(string $token): Response|RedirectResponse
    {
        $invitation = CompanyInvitation::where('token', $token)->first();

        if (! $invitation) {
            return redirect()->route('home')->with('toast', [
                'message' => __('company.invitation.invalid_token'),
                'type' => 'error',
            ]);
        }

        if ($invitation->isExpired()) {
            return redirect()->route('home')->with('toast', [
                'message' => __('company.invitation.expired'),
                'type' => 'error',
            ]);
        }

        if ($invitation->isAccepted()) {
            return redirect()->route('home')->with('toast', [
                'message' => __('company.invitation.already_accepted'),
                'type' => 'warning',
            ]);
        }

        // Check if user is already logged in
        if (Auth::check()) {
            $user = Auth::user();

            // Check if this invitation is for the logged-in user
            if ($user->email === $invitation->email) {
                // User is logged in and this is their invitation - show invitation with accept option
                return redirect()->route('invitations.show', $token)
                    ->with('message', __('company.invitation.logged_in_user'));
            } else {
                // User is logged in but this invitation is for a different email
                Auth::logout();

                return redirect()->route('invitations.show', $token)
                    ->with('message', __('company.invitation.wrong_user'));
            }
        }

        // User is not logged in - show invitation details
        $invitedUser = \App\Models\User::where('email', $invitation->email)->first();

        return Inertia::render('Invitations/Show', [
            'invitation' => [
                'token' => $invitation->token,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'expires_at' => $invitation->expires_at,
            ],
            'company' => [
                'id' => $invitation->company->id,
                'name' => $invitation->company->name,
            ],
            'inviter' => [
                'name' => $invitation->inviter->name,
                'email' => $invitation->inviter->email,
            ],
            'userExists' => $invitedUser !== null,
            'registerUrl' => route('register', ['email' => $invitation->email, 'invitation' => $token]),
            'loginUrl' => route('login', ['email' => $invitation->email]),
        ]);
    }

    /**
     * Accept an invitation (user must be authenticated)
     */
    public function accept(string $token): RedirectResponse
    {
        $user = Auth::user();

        try {
            $this->invitationService->acceptInvitation($token, $user);

            return redirect()->route('dashboard')->with('toast', [
                'message' => __('company.invitation.accepted_successfully'),
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
