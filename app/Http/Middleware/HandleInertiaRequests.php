<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
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
                    'is_admin' => $user->isAdminInCurrentCompany(),
                ]) : null,
            ],
            'toast' => fn () => $request->session()->get('toast'),
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
