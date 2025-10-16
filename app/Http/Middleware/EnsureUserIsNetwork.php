<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Services\UserService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNetwork
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Check if user has a current company
        if (! $user->currentCompany) {
            return redirect()->route('dashboard')->with('toast', [
                'message' => __('company.toast.need_company_first'),
                'type' => 'error',
            ]);
        }

        // Ensure relationships are loaded
        $user->load('companies', 'currentCompany');

        // Check if user has network role in current company
        if (! $this->userService->hasRole($user, $user->currentCompany, UserRole::NETWORK)) {
            return redirect()->route('dashboard')->with('toast', [
                'message' => __('company.toast.no_permission'),
                'type' => 'error',
            ]);
        }

        return $next($request);
    }
}
