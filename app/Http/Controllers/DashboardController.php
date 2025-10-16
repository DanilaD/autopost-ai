<?php

namespace App\Http\Controllers;

use App\Services\Post\PostService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * Display the dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $company = $user->currentCompany;

        // Get Instagram accounts count based on user context
        if ($company) {
            $instagramAccountsCount = $company->instagramAccounts()->count();
            $posts = $this->postService->getCompanyPosts($company->id, ['limit' => 5]);
        } else {
            // Individual user - get their own data
            $instagramAccountsCount = $user->instagramAccounts()->count();
            $posts = $this->postService->getByUser($user->id, ['limit' => 5]);
        }

        // Get scheduled posts count
        $scheduledPostsCount = $posts->where('status', 'scheduled')->count();

        // Get wallet balance (future feature)
        $walletBalance = 0;

        return Inertia::render('Dashboard', [
            'stats' => [
                'instagram_accounts' => $instagramAccountsCount,
                'scheduled_posts' => $scheduledPostsCount,
                'wallet_balance' => $walletBalance,
            ],
            'recentPosts' => $posts,
        ]);
    }
}
