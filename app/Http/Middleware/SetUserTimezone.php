<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to set the application timezone based on authenticated user's preference.
 *
 * This middleware ensures that all dates and times displayed to the user
 * are shown in their preferred timezone. The actual dates are still stored
 * in UTC in the database (Laravel default), but are converted for display.
 */
class SetUserTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // If user is authenticated and has a timezone preference, set it
        if (Auth::check() && Auth::user()->timezone) {
            config(['app.timezone' => Auth::user()->timezone]);
            date_default_timezone_set(Auth::user()->timezone);
        }

        return $next($request);
    }
}
