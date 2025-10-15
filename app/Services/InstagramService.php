<?php

namespace App\Services;

use App\Models\Company;
use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Instagram API Service
 *
 * Handles all Instagram Graph API interactions including:
 * - OAuth authentication
 * - Token exchange and refresh
 * - Profile data fetching
 */
class InstagramService
{
    protected ?string $clientId;

    protected ?string $clientSecret;

    protected ?string $redirectUri;

    protected string $graphApiUrl = 'https://graph.instagram.com';

    protected string $apiVersion = 'v18.0';

    public function __construct()
    {
        $this->clientId = config('services.instagram.client_id');
        $this->clientSecret = config('services.instagram.client_secret');
        $this->redirectUri = config('services.instagram.redirect_uri');

        // Validate Instagram credentials are configured
        if (! $this->clientId || ! $this->clientSecret || ! $this->redirectUri) {
            throw new \RuntimeException(
                'Instagram API credentials not configured. '.
                'Please add INSTAGRAM_CLIENT_ID, INSTAGRAM_CLIENT_SECRET, and INSTAGRAM_REDIRECT_URI to your .env file.'
            );
        }
    }

    /**
     * Check if using dummy/development credentials
     */
    public function isDummyCredentials(): bool
    {
        return str_starts_with($this->clientId, 'dummy_')
            || str_contains($this->clientId, 'dummy')
            || str_contains($this->clientId, 'test')
            || str_contains($this->clientId, 'fake');
    }

    /**
     * Get the Instagram OAuth authorization URL
     */
    public function getAuthorizationUrl(): string
    {
        $params = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'user_profile,user_media',
            'response_type' => 'code',
        ]);

        return "https://api.instagram.com/oauth/authorize?{$params}";
    }

    /**
     * Exchange authorization code for access token
     */
    public function getAccessToken(string $code): ?array
    {
        try {
            $response = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $this->redirectUri,
                'code' => $code,
            ]);

            if ($response->failed()) {
                Log::error('Instagram token exchange failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Instagram token exchange error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Exchange short-lived token for long-lived token (60 days)
     */
    public function getLongLivedToken(string $shortLivedToken): ?array
    {
        try {
            $response = Http::get("{$this->graphApiUrl}/access_token", [
                'grant_type' => 'ig_exchange_token',
                'client_secret' => $this->clientSecret,
                'access_token' => $shortLivedToken,
            ]);

            if ($response->failed()) {
                Log::error('Instagram long-lived token exchange failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Instagram long-lived token error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Refresh a long-lived token (before expiry)
     */
    public function refreshToken(string $accessToken): ?array
    {
        try {
            $response = Http::get("{$this->graphApiUrl}/refresh_access_token", [
                'grant_type' => 'ig_refresh_token',
                'access_token' => $accessToken,
            ]);

            if ($response->failed()) {
                Log::error('Instagram token refresh failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Instagram token refresh error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get user profile information
     */
    public function getUserProfile(string $accessToken, string $userId = 'me'): ?array
    {
        try {
            $response = Http::get("{$this->graphApiUrl}/{$userId}", [
                'fields' => 'id,username,account_type,media_count',
                'access_token' => $accessToken,
            ]);

            if ($response->failed()) {
                Log::error('Instagram profile fetch failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Instagram profile fetch error', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Connect an Instagram account to a company
     *
     * @deprecated Use connectAccountForCompany instead
     */
    public function connectAccount(Company $company, string $code): ?InstagramAccount
    {
        return $this->connectAccountForCompany($company, $code);
    }

    /**
     * Connect an Instagram account to a company
     */
    public function connectAccountForCompany(Company $company, string $code): ?InstagramAccount
    {
        $accountData = $this->exchangeCodeForAccountData($code);

        if (! $accountData) {
            return null;
        }

        // Create or update Instagram account as company-owned
        $account = InstagramAccount::updateOrCreate(
            [
                'instagram_user_id' => $accountData['profile']['id'],
            ],
            [
                'company_id' => $company->id,
                'user_id' => null,
                'ownership_type' => 'company',
                'username' => $accountData['profile']['username'],
                'access_token' => $accountData['access_token'],
                'token_expires_at' => $accountData['expires_at'],
                'account_type' => $accountData['profile']['account_type'] ?? 'personal',
                'status' => 'active',
                'last_synced_at' => now(),
                'is_shared' => false,
                'metadata' => [
                    'media_count' => $accountData['profile']['media_count'] ?? 0,
                ],
            ]
        );

        return $account;
    }

    /**
     * Connect an Instagram account to a user
     */
    public function connectAccountForUser(User $user, string $code): ?InstagramAccount
    {
        $accountData = $this->exchangeCodeForAccountData($code);

        if (! $accountData) {
            return null;
        }

        // Create or update Instagram account as user-owned
        $account = InstagramAccount::updateOrCreate(
            [
                'instagram_user_id' => $accountData['profile']['id'],
            ],
            [
                'user_id' => $user->id,
                'company_id' => null,
                'ownership_type' => 'user',
                'username' => $accountData['profile']['username'],
                'access_token' => $accountData['access_token'],
                'token_expires_at' => $accountData['expires_at'],
                'account_type' => $accountData['profile']['account_type'] ?? 'personal',
                'status' => 'active',
                'last_synced_at' => now(),
                'is_shared' => false,
                'metadata' => [
                    'media_count' => $accountData['profile']['media_count'] ?? 0,
                ],
            ]
        );

        return $account;
    }

    /**
     * Exchange authorization code for account data (shared logic)
     *
     * @return array{access_token: string, expires_at: \Carbon\Carbon, profile: array}|null
     */
    private function exchangeCodeForAccountData(string $code): ?array
    {
        // Step 1: Exchange code for short-lived token
        $tokenData = $this->getAccessToken($code);
        if (! $tokenData || ! isset($tokenData['access_token'])) {
            return null;
        }

        $shortLivedToken = $tokenData['access_token'];
        $userId = $tokenData['user_id'];

        // Step 2: Exchange for long-lived token (60 days)
        $longLivedData = $this->getLongLivedToken($shortLivedToken);
        if (! $longLivedData || ! isset($longLivedData['access_token'])) {
            return null;
        }

        $accessToken = $longLivedData['access_token'];
        $expiresIn = $longLivedData['expires_in'] ?? 5184000; // 60 days default

        // Step 3: Get user profile
        $profile = $this->getUserProfile($accessToken, $userId);
        if (! $profile) {
            return null;
        }

        return [
            'access_token' => $accessToken,
            'expires_at' => now()->addSeconds($expiresIn),
            'profile' => $profile,
        ];
    }

    /**
     * Disconnect an Instagram account
     */
    public function disconnectAccount(InstagramAccount $account): bool
    {
        $account->markAsDisconnected();

        return true;
    }

    /**
     * Sync profile data for an account
     */
    public function syncProfile(InstagramAccount $account): bool
    {
        try {
            $profile = $this->getUserProfile($account->access_token, $account->instagram_user_id);

            if (! $profile) {
                return false;
            }

            $account->update([
                'username' => $profile['username'],
                'account_type' => $profile['account_type'] ?? 'personal',
                'last_synced_at' => now(),
                'metadata' => array_merge($account->metadata ?? [], [
                    'media_count' => $profile['media_count'] ?? 0,
                ]),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Instagram profile sync error', [
                'account_id' => $account->id,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
