<?php

namespace App\Console\Commands\Instagram;

use App\Models\InstagramAccount;
use App\Services\InstagramService;
use Illuminate\Console\Command;

class RefreshInstagramTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'instagram:refresh-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh Instagram access tokens that are expiring soon';

    /**
     * Execute the console command.
     */
    public function handle(InstagramService $instagramService): int
    {
        $this->info('Starting Instagram token refresh...');

        // Get accounts with tokens expiring within 7 days
        $accounts = InstagramAccount::active()
            ->expiringSoon()
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('✓ No tokens need refreshing.');

            return Command::SUCCESS;
        }

        $this->info("Found {$accounts->count()} token(s) to refresh.");

        $refreshed = 0;
        $failed = 0;

        foreach ($accounts as $account) {
            $this->info("Refreshing token for @{$account->username}...");

            try {
                $tokenData = $instagramService->refreshToken($account->access_token);

                if ($tokenData && isset($tokenData['access_token'])) {
                    $account->update([
                        'access_token' => $tokenData['access_token'],
                        'token_expires_at' => now()->addSeconds($tokenData['expires_in']),
                    ]);

                    $this->info("  ✓ Refreshed successfully (expires: {$account->token_expires_at->format('Y-m-d')})");
                    $refreshed++;
                } else {
                    $this->error('  ✗ Failed to refresh token');
                    $account->markAsExpired();
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->error("  ✗ Error: {$e->getMessage()}");
                $account->markAsExpired();
                $failed++;
            }
        }

        $this->newLine();
        $this->info('Refresh complete:');
        $this->info("  ✓ Refreshed: {$refreshed}");
        if ($failed > 0) {
            $this->warn("  ✗ Failed: {$failed}");
        }

        return Command::SUCCESS;
    }
}
