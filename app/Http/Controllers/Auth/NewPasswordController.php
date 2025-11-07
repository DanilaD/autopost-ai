<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->email,
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        // If the password was successfully reset, we will automatically log the user in
        // and redirect them to the dashboard. This provides a better user experience.
        if ($status == Password::PASSWORD_RESET) {
            // Find the user and log them in automatically
            $user = \App\Models\User::where('email', $request->email)->first();

            if ($user) {
                Auth::login($user);
                $request->session()->regenerate();

                return redirect()->intended(route('dashboard', absolute: false))
                    ->with('toast', [
                        'type' => 'success',
                        'message' => __('passwords.reset'),
                    ]);
            }

            // Fallback to login page if user not found (shouldn't happen)
            return redirect()->route('login')->with('status', __('passwords.reset'));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
