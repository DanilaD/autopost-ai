<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Preserve language preference from guest session to authenticated user
        $user = $request->user();

        // If user doesn't have a saved locale, use the current session/LocalStorage locale
        if (! $user->locale) {
            $currentLocale = app()->getLocale();
            if (in_array($currentLocale, ['en', 'ru', 'es'])) {
                $user->update(['locale' => $currentLocale]);
            }
        }

        return redirect()->intended(route('dashboard', absolute: false))
            ->with('toast', [
                'type' => 'success',
                'message' => __('auth.login_success'),
            ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
