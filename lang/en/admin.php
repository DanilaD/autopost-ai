<?php

return [
    // Inquiry Management
    'inquiries' => [
        'title' => 'Inquiry Management',
        'email' => 'Email',
        'ip_address' => 'IP Address',
        'user_agent' => 'User Agent',
        'created_at' => 'Created At',
        'delete' => 'Delete',
        'export' => 'Export CSV',
        'export_button' => 'Export to CSV',
        'search' => 'Search by email...',
        'search_placeholder' => 'Search inquiries...',
        'no_results' => 'No inquiries found',
        'total' => 'Total Inquiries',
        'today' => 'Today',
        'this_week' => 'This Week',
        'this_month' => 'This Month',
        'delete_confirm' => 'Are you sure you want to delete this inquiry?',
        'delete_success' => 'Inquiry deleted successfully',
        'delete_error' => 'Failed to delete inquiry',
    ],

    // User Management
    'users' => [
        'title' => 'User Management',
        'name' => 'Name',
        'email' => 'Email',
        'role' => 'Role',
        'status' => 'Status',
        'last_login' => 'Last Login',
        'stats' => 'Statistics',
        'actions' => 'Actions',
        'search' => 'Search users...',
        'search_placeholder' => 'Search by name or email...',
        'no_results' => 'No users found',
        'total_users' => 'Total Users',
        'active_users' => 'Active',
        'suspended_users' => 'Suspended',
        'new_this_month' => 'New This Month',

        // Actions
        'send_password_reset' => 'Send Password Reset',
        'suspend' => 'Suspend User',
        'unsuspend' => 'Unsuspend User',
        'impersonate' => 'Impersonate',
        'view_details' => 'View Details',

        // Status
        'active' => 'Active',
        'suspended' => 'Suspended',
        'suspended_by' => 'Suspended by',
        'suspended_on' => 'Suspended on',
        'suspension_reason' => 'Reason',

        // Stats
        'companies_count' => 'Companies',
        'instagram_accounts' => 'Instagram Accounts',
        'posts_count' => 'Posts',
        'account_age' => 'Account Age',
        'never_logged_in' => 'Never logged in',

        // Modals
        'suspend_modal_title' => 'Suspend User',
        'suspend_modal_message' => 'Please provide a reason for suspension:',
        'confirm_suspend' => 'Suspend',
        'cancel' => 'Cancel',
        'confirm_impersonate_title' => 'Impersonate User',
        'confirm_impersonate_message' => 'You are about to view the application as this user. Continue?',
        'confirm' => 'Confirm',

        // Messages
        'password_reset_sent' => 'Password reset link sent successfully',
        'suspend_success' => 'User suspended successfully',
        'unsuspend_success' => 'User access restored successfully',
        'cannot_suspend_self' => 'You cannot suspend yourself',
        'cannot_suspend_admin' => 'You cannot suspend an administrator',
    ],

    // Impersonation
    'impersonating' => 'You are impersonating',
    'impersonating_as' => 'Impersonating as',
    'stop_impersonation' => 'Stop Impersonation',
    'return_to_admin' => 'Return to Admin Account',
    'impersonation_started' => 'You are now impersonating the user',
    'impersonation_ended' => 'Impersonation ended',

    // Common
    'filter' => 'Filter',
    'sort' => 'Sort',
    'sort_by' => 'Sort by',
    'direction' => 'Direction',
    'ascending' => 'Ascending',
    'descending' => 'Descending',
    'per_page' => 'Per Page',
    'showing' => 'Showing',
    'to' => 'to',
    'of' => 'of',
    'results' => 'results',
    'loading' => 'Loading...',
    'no_data' => 'No data available',
];
