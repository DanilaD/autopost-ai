<?php

namespace App\Http\Controllers\Instagram;

use App\Http\Controllers\Controller;
use App\Services\InstagramService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class InstagramOAuthController extends Controller
{
    public function __construct(
        protected InstagramService $instagramService
    ) {}

    /**
     * Redirect to Instagram OAuth authorization page
     */
    public function redirect(): RedirectResponse
    {
        $authUrl = $this->instagramService->getAuthorizationUrl();

        return redirect($authUrl);
    }

    /**
     * Handle OAuth callback from Instagram
     */
    public function callback(Request $request): RedirectResponse
    {
        // Check for errors
        if ($request->has('error')) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Instagram connection was cancelled or failed.',
                ]);
        }

        // Get authorization code
        $code = $request->input('code');
        if (! $code) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Authorization code not received from Instagram.',
                ]);
        }

        // Get user's current company
        $user = $request->user();
        $company = $user->currentCompany;

        if (! $company) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'No active company found. Please select or create a company first.',
                ]);
        }

        // Connect Instagram account
        $account = $this->instagramService->connectAccount($company, $code);

        if (! $account) {
            return redirect()->route('instagram.index')
                ->with('toast', [
                    'type' => 'error',
                    'message' => 'Failed to connect Instagram account. Please try again.',
                ]);
        }

        return redirect()->route('instagram.index')
            ->with('toast', [
                'type' => 'success',
                'message' => "Instagram account @{$account->username} connected successfully!",
            ]);
    }
}
