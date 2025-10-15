<?php

return [
    // Page titles
    'title' => 'Instagram Accounts',
    'connect_title' => 'Connect Instagram Account',

    // Messages
    'no_accounts' => 'No Instagram accounts connected yet',
    'connect_description' => 'Connect your Instagram account to start automating your content publishing.',
    'connect_button' => 'Connect Instagram Account',

    // Success messages
    'connected_success' => 'Instagram account connected successfully!',
    'disconnected_success' => 'Instagram account disconnected successfully.',
    'synced_success' => 'Instagram account data synced successfully.',

    // Error messages
    'not_configured' => 'Instagram integration is not configured yet. Please contact your administrator to set up Instagram API credentials.',
    'no_active_company' => 'You need to have an active company to connect Instagram accounts. Please create or select a company first.',
    'connection_failed' => 'Failed to connect Instagram account. Please try again.',
    'token_expired' => 'Your Instagram token has expired. Please reconnect your account.',
    'sync_failed' => 'Failed to sync Instagram account data.',
    'disconnect_failed' => 'Failed to disconnect Instagram account.',
    'dummy_credentials_warning' => '🔧 Instagram is in development mode. To connect a real Instagram account, please add your Instagram App credentials to the .env file. Contact your developer or administrator for assistance.',

    // Account info
    'username' => 'Username',
    'followers' => 'Followers',
    'following' => 'Following',
    'posts' => 'Posts',
    'status' => 'Status',
    'connected_at' => 'Connected',
    'last_synced' => 'Last Synced',

    // Actions
    'sync' => 'Sync',
    'disconnect' => 'Disconnect',
    'refresh' => 'Refresh',
    'disconnect_confirm' => 'Are you sure you want to disconnect @:username? You can reconnect it anytime.',
    'disconnect_confirm_title' => 'Disconnect Instagram Account?',
    'disconnect_confirm_message' => 'Are you sure you want to disconnect {username}? You can reconnect it anytime.',
    'disconnect_button' => 'Yes, Disconnect',
    'disconnect_cancel' => 'Cancel',

    // Status
    'status_active' => 'Active',
    'status_expired' => 'Expired',
    'status_expiring_soon' => 'Expiring Soon',
    'status_error' => 'Error',

    // Account details
    'connected' => 'Connected',
    'account_type' => 'Account Type',
    'token_warning' => 'Your access token will expire soon. Please reconnect this account to continue posting.',
];
