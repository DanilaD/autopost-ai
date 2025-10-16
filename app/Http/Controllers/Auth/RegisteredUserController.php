<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        // Get pre-filled email and invitation token from query string
        $email = request('email');
        $invitationToken = request('invitation');

        return Inertia::render('Auth/Register', [
            'email' => $email,
            'invitationToken' => $invitationToken,
        ]);
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'timezone' => 'nullable|string|timezone',
            'locale' => 'nullable|string|in:en,ru,es',
            'invitation_token' => 'nullable|string',
        ]);

        // Get locale from request or session, default to 'en'
        $locale = $validated['locale'] ?? session('locale', 'en');
        if (! in_array($locale, ['en', 'ru', 'es'])) {
            $locale = 'en';
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'timezone' => $validated['timezone'] ?? 'UTC',
            'locale' => $locale,
        ]);

        event(new Registered($user));

        Auth::login($user);

        // Handle invitation acceptance if token is provided
        if (! empty($validated['invitation_token'])) {
            try {
                $invitationService = app(\App\Services\CompanyInvitationService::class);
                $invitationService->acceptInvitation($validated['invitation_token'], $user);

                return redirect(route('dashboard', absolute: false))->with('toast', [
                    'message' => __('company.invitation.accepted_successfully'),
                    'type' => 'success',
                ]);
            } catch (\Exception $e) {
                // If invitation acceptance fails, still redirect to dashboard
                // The user is registered and logged in, just not added to company
                return redirect(route('dashboard', absolute: false))->with('toast', [
                    'message' => __('company.invitation.accepted_successfully').' '.$e->getMessage(),
                    'type' => 'warning',
                ]);
            }
        }

        return redirect(route('dashboard', absolute: false));
    }
}
