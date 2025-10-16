<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Services\UserService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? array_merge($user->toArray(), [
                    'is_admin' => $this->userService->isAdminInCurrentCompany($user),
                    'current_company' => $user->currentCompany ? [
                        'id' => $user->currentCompany->id,
                        'name' => $user->currentCompany->name,
                        'user_role' => $this->userService->getRoleIn($user, $user->currentCompany)?->value,
                        'is_network' => $this->userService->hasRole($user, $user->currentCompany, UserRole::NETWORK),
                        'is_admin' => $this->userService->hasRole($user, $user->currentCompany, UserRole::ADMIN),
                    ] : null,
                ]) : null,
            ],
            'toast' => fn () => $request->session()->pull('toast'),
            'locale' => app()->getLocale(),
            'impersonating' => fn () => $request->session()->get('impersonate') ? [
                'admin_id' => $request->session()->get('impersonate.admin_id'),
                'admin_name' => $request->session()->get('impersonate.admin_name'),
                'admin_email' => $request->session()->get('impersonate.admin_email'),
                'started_at' => $request->session()->get('impersonate.started_at'),
            ] : null,
        ];
    }
}
