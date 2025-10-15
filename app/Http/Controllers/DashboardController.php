<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $company = $user->currentCompany;

        // Get Instagram accounts count for current company
        $instagramAccountsCount = $company 
            ? $company->instagramAccounts()->count() 
            : 0;

        // Get scheduled posts count (future feature)
        $scheduledPostsCount = 0;

        // Get wallet balance (future feature)
        $walletBalance = 0;

        return Inertia::render('Dashboard', [
            'stats' => [
                'instagram_accounts' => $instagramAccountsCount,
                'scheduled_posts' => $scheduledPostsCount,
                'wallet_balance' => $walletBalance,
            ],
        ]);
    }
}

