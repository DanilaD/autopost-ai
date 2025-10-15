<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * Ensures the authenticated user has admin privileges in their current company.
     * Redirects to dashboard with error if not authorized.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (! $request->user()) {
            abort(403, 'Authentication required.');
        }

        // Check if user has admin role in current company
        if (! $request->user()->isAdminInCurrentCompany()) {
            abort(403, 'This action requires administrator privileges.');
        }

        return $next($request);
    }
}
