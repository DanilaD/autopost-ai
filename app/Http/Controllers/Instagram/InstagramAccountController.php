<?php

namespace App\Http\Controllers\Instagram;

use App\Http\Controllers\Controller;
use App\Models\InstagramAccount;
use App\Services\InstagramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InstagramAccountController extends Controller
{
    protected ?InstagramService $instagramService = null;

    protected bool $instagramConfigured = true;

    protected ?string $configError = null;

    public function __construct()
    {
        try {
            $this->instagramService = app(InstagramService::class);
        } catch (\RuntimeException $e) {
            $this->instagramConfigured = false;
            $this->configError = 'Instagram integration is not configured yet. Please contact your administrator to set up Instagram API credentials.';
        }
    }

    /**
     * Display list of Instagram accounts
     */
    public function index(Request $request): Response
    {
        // Check if Instagram is configured
        if (! $this->instagramConfigured) {
            return Inertia::render('Instagram/Index', [
                'accounts' => [],
                'hasCompany' => (bool) $request->user()->currentCompany,
                'configError' => $this->configError,
            ]);
        }

        $user = $request->user();
        $company = $user->currentCompany;

        $accounts = $company
            ? $company->instagramAccounts()
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(fn ($account) => [
                    'id' => $account->id,
                    'username' => $account->username,
                    'instagram_user_id' => $account->instagram_user_id,
                    'account_type' => $account->account_type,
                    'profile_picture_url' => $account->profile_picture_url,
                    'followers_count' => $account->followers_count,
                    'status' => $account->status,
                    'is_active' => $account->isActive(),
                    'is_token_expired' => $account->isTokenExpired(),
                    'is_token_expiring_soon' => $account->isTokenExpiringSoon(),
                    'token_expires_at' => $account->token_expires_at->format('Y-m-d H:i:s'),
                    'last_synced_at' => $account->last_synced_at?->format('Y-m-d H:i:s'),
                    'created_at' => $account->created_at->format('Y-m-d H:i:s'),
                ])
            : collect();

        return Inertia::render('Instagram/Index', [
            'accounts' => $accounts,
            'hasCompany' => (bool) $company,
        ]);
    }

    /**
     * Disconnect an Instagram account
     */
    public function disconnect(Request $request, InstagramAccount $account): RedirectResponse
    {
        // Check if Instagram is configured
        if (! $this->instagramConfigured) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => $this->configError,
                ]);
        }

        // Check if user has access to this account
        $user = $request->user();
        $company = $user->currentCompany;

        if (! $company || $account->company_id !== $company->id) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Unauthorized action.',
                ]);
        }

        $username = $account->username;
        $this->instagramService->disconnectAccount($account);

        return redirect()->route('instagram.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Instagram account @{$username} disconnected successfully.",
            ]);
    }

    /**
     * Sync an Instagram account
     */
    public function sync(Request $request, InstagramAccount $account): RedirectResponse
    {
        // Check if Instagram is configured
        if (! $this->instagramConfigured) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => $this->configError,
                ]);
        }

        // Check if user has access to this account
        $user = $request->user();
        $company = $user->currentCompany;

        if (! $company || $account->company_id !== $company->id) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Unauthorized action.',
                ]);
        }

        $success = $this->instagramService->syncProfile($account);

        if ($success) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'success',
                    'message' => "Account @{$account->username} synced successfully.",
                ]);
        }

        return redirect()->route('instagram.index')
            ->with('toast', [
                'type' => 'error',
                'message' => 'Failed to sync account. Please try again.',
            ]);
    }
}
