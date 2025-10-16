<?php

namespace App\Http\Middleware;

use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function __construct(
        private UserService $userService
    ) {}

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
            return redirect()->route('home');
        }

        // Check if user has admin role in current company
        if (! $this->userService->isAdminInCurrentCompany($request->user())) {
            abort(403, 'This action requires administrator privileges.');
        }

        return $next($request);
    }
}
