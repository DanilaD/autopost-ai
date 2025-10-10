<?php

namespace App\Http\Controllers\Instagram;

use App\Http\Controllers\Controller;
use App\Services\InstagramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InstagramOAuthController extends Controller
{
    protected ?InstagramService $instagramService = null;

    public function __construct()
    {
        try {
            $this->instagramService = app(InstagramService::class);
        } catch (\RuntimeException $e) {
            $this->instagramService = null;
        }
    }

    /**
     * Redirect to Instagram OAuth authorization page
     */
    public function redirect(): RedirectResponse
    {
        // Check if Instagram is configured
        if (! $this->instagramService) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => __('instagram.not_configured'),
                ]);
        }

        $authUrl = $this->instagramService->getAuthorizationUrl();

        return redirect($authUrl);
    }

    /**
     * Handle OAuth callback from Instagram
     */
    public function callback(Request $request): RedirectResponse
    {
        // Check if Instagram is configured
        if (! $this->instagramService) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => __('instagram.not_configured'),
                ]);
        }

        // Check for errors
        if ($request->has('error')) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => __('instagram.connection_failed'),
                ]);
        }

        // Get authorization code
        $code = $request->input('code');
        if (! $code) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => __('instagram.connection_failed'),
                ]);
        }

        // Get user's current company
        $user = $request->user();
        $company = $user->currentCompany;

        if (! $company) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => __('instagram.no_active_company'),
                ]);
        }

        // Connect Instagram account
        $account = $this->instagramService->connectAccount($company, $code);

        if (! $account) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => __('instagram.connection_failed'),
                ]);
        }

        return redirect()->route('instagram.index')
            ->with('toast', [
                'type' => 'success',
                'message' => __('instagram.connected_success'),
            ]);
    }
}
