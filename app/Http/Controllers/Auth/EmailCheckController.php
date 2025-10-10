<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class EmailCheckController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        // Rate limiting: 5 attempts per minute per IP
        $key = 'email-check:'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => __('Too many attempts. Please try again in a minute.'),
            ]);
        }

        RateLimiter::hit($key, 60);

        // Validate email
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $email = $validated['email'];

        // Check if user exists
        $user = User::where('email', $email)->first();

        if (! $user) {
            // Log inquiry for non-existent email (marketing intelligence)
            Inquiry::create([
                'email' => $email,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
            ]);

            // Redirect to registration with email pre-filled
            return redirect()->route('register', ['email' => $email])
                ->with('message', __('auth.new_here'));
        }

        // User exists - redirect to login with email
        return redirect()->route('login', ['email' => $email])
            ->with('message', __('auth.welcome_back'));
    }
}
