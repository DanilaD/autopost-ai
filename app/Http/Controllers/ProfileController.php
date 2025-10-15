<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $timezoneService = new \App\Services\TimezoneService;
        $user = $request->user();
        $company = $user->currentCompany;

        // Get company statistics
        $companyStats = null;
        if ($company) {
            $companyStats = [
                'instagram_accounts_count' => $company->instagramAccounts()->count(),
                'team_members_count' => $company->users()->count(),
                'user_role' => $company->getUserRole($user),
            ];
        }

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'timezones' => $timezoneService->getFlatTimezones(),
            'commonTimezones' => $timezoneService->getCommonTimezones(),
            'company' => $company ? [
                'id' => $company->id,
                'name' => $company->name,
                'created_at' => $company->created_at->format('M j, Y'),
                'stats' => $companyStats,
            ] : null,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
