<?php

return [
    'title' => 'Dashboard',
    'greeting' => [
        'morning' => 'Good morning',
        'afternoon' => 'Good afternoon',
        'evening' => 'Good evening',
    ],
    // Rotating welcome messages - randomly displayed on dashboard
    'welcome_messages' => [
        'Welcome to '.config('app.name').'. Let\'s make something amazing today.',
        "Ready to automate your social media? Let's get started!",
        "Your content deserves to shine. Let's make it happen.",
        'Time to turn your ideas into engaging posts.',
        'Great content starts here. What will you create today?',
        "Let's grow your Instagram presence together.",
        'Welcome back! Your audience is waiting for your next post.',
    ],
    'stats' => [
        'instagram_accounts' => 'Instagram Accounts',
        'scheduled_posts' => 'Scheduled Posts',
        'wallet_balance' => 'Wallet Balance',
    ],
    'actions' => [
        'connect_instagram' => 'Connect Instagram',
        'connect_instagram_desc' => 'Link your Instagram account to start automating your content.',
        'connect_now' => 'Connect Now',
        // Create Post action (coming soon)
        'create_post' => 'Create Post',
        'create_post_desc' => 'Schedule and publish content across your Instagram accounts.',
        'coming_soon' => 'COMING SOON',
    ],
    'empty_state' => [
        'no_posts' => 'No posts yet',
        'get_started' => 'Get started by connecting your Instagram account and creating your first post.',
    ],
    'recent_posts' => 'Recent Posts',
    'view_all_posts' => 'View all posts',
];
