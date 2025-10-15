# Instagram Account Management Guide

**Date:** October 10, 2025  
**Version:** 1.0

---

## 📋 Your Current Accounts

```
ID: 1 | @dan_test_instagram    | 5,280 followers
ID: 2 | @my_second_instagram   | 44,134 followers
```

---

## ➕ How to Add New Instagram Accounts

### Method 1: Quick Add (Copy & Paste) ⭐

**Step 1:** Open terminal in your project:
```bash
cd /Users/daniladolmatov/Sites/autopost-ai
php artisan tinker
```

**Step 2:** Copy and paste this code:
```php
// Get your user and company
$user = \App\Models\User::where('email', 'd6174173844@gmail.com')->first();
$company = $user->currentCompany;

// Create a new Instagram account
$account = \App\Models\InstagramAccount::create([
    'company_id' => $company->id,
    'user_id' => $user->id,
    'username' => 'your_instagram_name',  // ← Change this
    'instagram_user_id' => rand(100000000, 999999999),
    'access_token' => encrypt('fake_token_' . time()),
    'token_expires_at' => now()->addDays(60),
    'account_type' => 'business',  // or 'personal'
    'profile_picture_url' => 'https://i.pravatar.cc/150?img=' . rand(1, 70),
    'followers_count' => rand(1000, 100000),
    'status' => 'active',
    'ownership_type' => 'company',
    'is_shared' => false,
]);

echo "✅ Created @{$account->username} with " . number_format($account->followers_count) . " followers\n";
```

**Step 3:** Exit tinker:
```php
exit
```

**Step 4:** Refresh your browser → New account appears!

---

### Method 2: One-Line Command (Fastest) 🚀

Copy and paste this entire command:

```bash
cd /Users/daniladolmatov/Sites/autopost-ai && php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'd6174173844@gmail.com')->first();
\$company = \$user->currentCompany;
\$account = \App\Models\InstagramAccount::create([
    'company_id' => \$company->id,
    'user_id' => \$user->id,
    'username' => 'new_account_name',
    'instagram_user_id' => rand(100000000, 999999999),
    'access_token' => encrypt('fake_token_' . time()),
    'token_expires_at' => now()->addDays(60),
    'account_type' => 'business',
    'profile_picture_url' => 'https://i.pravatar.cc/150?img=' . rand(1, 70),
    'followers_count' => rand(5000, 50000),
    'status' => 'active',
    'ownership_type' => 'company',
    'is_shared' => false,
]);
echo '✅ Created @' . \$account->username . ' with ' . number_format(\$account->followers_count) . ' followers';
"
```

**Don't forget to change:** `'username' => 'new_account_name'`

---

### Method 3: Customized Account (Full Control) 🎨

For specific followers, account type, status, etc:

```bash
php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'd6174173844@gmail.com')->first();
$company = $user->currentCompany;

$account = \App\Models\InstagramAccount::create([
    'company_id' => $company->id,
    'user_id' => $user->id,
    
    // Basic Info
    'username' => 'fitness_guru_2024',
    'instagram_user_id' => '123456789',
    
    // Account Details
    'account_type' => 'business',  // 'business' or 'personal'
    'profile_picture_url' => 'https://i.pravatar.cc/150?img=45',
    'followers_count' => 250000,  // Set exact number
    
    // Status
    'status' => 'active',  // 'active', 'expired', 'error', 'disconnected'
    
    // Ownership
    'ownership_type' => 'company',  // 'company' or 'user'
    'is_shared' => false,
    
    // Token (for testing)
    'access_token' => encrypt('fake_token_fitness_guru'),
    'token_expires_at' => now()->addDays(60),
    
    // Optional: Metadata
    'metadata' => [
        'bio' => 'Fitness coach & nutritionist',
        'website' => 'https://example.com',
        'posts_count' => 1234,
    ],
]);

echo "✅ Created @{$account->username}\n";
exit
```

---

## ➖ How to Remove Instagram Accounts

### Method 1: Via UI (Easiest) ⭐

1. **Go to Instagram page:** `/instagram`
2. **Find the account** you want to remove
3. **Click "Disconnect" button**
4. **Confirm** the action
5. ✅ **Done!** Account removed

**This is the recommended way!** It uses the built-in controller with proper validation.

---

### Method 2: Via Command (Quick) 🚀

**Step 1:** List your accounts to get the ID:
```bash
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'd6174173844@gmail.com')->first();
\$accounts = \$user->currentCompany->instagramAccounts;
foreach (\$accounts as \$account) {
    echo 'ID: ' . \$account->id . ' | @' . \$account->username . PHP_EOL;
}
"
```

**Step 2:** Remove by ID (replace `2` with the ID you want to remove):
```bash
php artisan tinker --execute="
\$account = \App\Models\InstagramAccount::find(2);
if (\$account) {
    \$username = \$account->username;
    \$account->delete();
    echo '✅ Removed @' . \$username . PHP_EOL;
} else {
    echo '❌ Account not found' . PHP_EOL;
}
"
```

**Or remove by username:**
```bash
php artisan tinker --execute="
\$account = \App\Models\InstagramAccount::where('username', 'my_second_instagram')->first();
if (\$account) {
    \$account->delete();
    echo '✅ Removed @' . \$account->username . PHP_EOL;
} else {
    echo '❌ Account not found' . PHP_EOL;
}
"
```

---

### Method 3: Remove All Accounts (Careful!) ⚠️

**WARNING:** This removes ALL Instagram accounts for your company!

```bash
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'd6174173844@gmail.com')->first();
\$count = \$user->currentCompany->instagramAccounts()->count();
\$user->currentCompany->instagramAccounts()->delete();
echo '✅ Removed ' . \$count . ' Instagram accounts' . PHP_EOL;
"
```

---

## 📊 List All Accounts

### Simple List

```bash
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'd6174173844@gmail.com')->first();
\$accounts = \$user->currentCompany->instagramAccounts;
foreach (\$accounts as \$account) {
    echo 'ID: ' . \$account->id . ' | @' . \$account->username . ' | ' . number_format(\$account->followers_count) . ' followers' . PHP_EOL;
}
"
```

### Detailed List

```bash
php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'd6174173844@gmail.com')->first();
$accounts = $user->currentCompany->instagramAccounts;

echo "📋 Instagram Accounts for {$user->currentCompany->name}\n";
echo "═══════════════════════════════════════════════\n\n";

foreach ($accounts as $account) {
    echo "ID: {$account->id}\n";
    echo "Username: @{$account->username}\n";
    echo "Followers: " . number_format($account->followers_count) . "\n";
    echo "Type: {$account->account_type}\n";
    echo "Status: {$account->status}\n";
    echo "Ownership: {$account->ownership_type}\n";
    echo "Token Expires: {$account->token_expires_at->format('M d, Y')}\n";
    echo "───────────────────────────────────────────\n\n";
}

exit
```

---

## 🎨 Account Customization Options

### Account Types
```php
'account_type' => 'business'   // Business account
'account_type' => 'personal'   // Personal account
```

### Status Options
```php
'status' => 'active'        // ✅ Working normally
'status' => 'expired'       // ⚠️ Token expired
'status' => 'error'         // ❌ Has errors
'status' => 'disconnected'  // 🔌 Manually disconnected
```

### Ownership Types
```php
'ownership_type' => 'company'  // Belongs to company (team)
'ownership_type' => 'user'     // Belongs to individual user
```

### Sharing
```php
'is_shared' => false  // Private to company
'is_shared' => true   // Shared with specific users
```

---

## 🎭 Create Accounts with Different Personas

### Fitness Influencer
```php
\App\Models\InstagramAccount::create([
    'company_id' => $company->id,
    'user_id' => $user->id,
    'username' => 'fitness_motivation_daily',
    'instagram_user_id' => rand(100000000, 999999999),
    'account_type' => 'business',
    'followers_count' => 125000,
    'profile_picture_url' => 'https://i.pravatar.cc/150?img=12',
    'access_token' => encrypt('fake_token_' . time()),
    'token_expires_at' => now()->addDays(60),
    'status' => 'active',
    'ownership_type' => 'company',
    'metadata' => [
        'bio' => '💪 Fitness Coach | 🥗 Nutrition Expert | 📍 LA',
        'category' => 'Health & Wellness',
    ],
]);
```

### Travel Blogger
```php
\App\Models\InstagramAccount::create([
    'company_id' => $company->id,
    'user_id' => $user->id,
    'username' => 'wanderlust_adventures',
    'instagram_user_id' => rand(100000000, 999999999),
    'account_type' => 'personal',
    'followers_count' => 85000,
    'profile_picture_url' => 'https://i.pravatar.cc/150?img=32',
    'access_token' => encrypt('fake_token_' . time()),
    'token_expires_at' => now()->addDays(60),
    'status' => 'active',
    'ownership_type' => 'user',
    'metadata' => [
        'bio' => '✈️ Travel Photographer | 🌍 42 Countries | 📷 Canon',
        'category' => 'Travel',
    ],
]);
```

### Food Blogger
```php
\App\Models\InstagramAccount::create([
    'company_id' => $company->id,
    'user_id' => $user->id,
    'username' => 'tasty_bites_daily',
    'instagram_user_id' => rand(100000000, 999999999),
    'account_type' => 'business',
    'followers_count' => 250000,
    'profile_picture_url' => 'https://i.pravatar.cc/150?img=25',
    'access_token' => encrypt('fake_token_' . time()),
    'token_expires_at' => now()->addDays(60),
    'status' => 'active',
    'ownership_type' => 'company',
    'metadata' => [
        'bio' => '🍕 Food Enthusiast | 👨‍🍳 Recipe Creator | 📍 NYC',
        'category' => 'Food & Beverage',
    ],
]);
```

---

## 🔄 Bulk Operations

### Create Multiple Accounts at Once

```bash
php artisan tinker
```

```php
$user = \App\Models\User::where('email', 'd6174173844@gmail.com')->first();
$company = $user->currentCompany;

$accounts = [
    ['username' => 'tech_reviews_pro', 'followers' => 45000, 'type' => 'business'],
    ['username' => 'fashion_trends_24', 'followers' => 89000, 'type' => 'business'],
    ['username' => 'gaming_highlights', 'followers' => 125000, 'type' => 'personal'],
];

foreach ($accounts as $data) {
    $account = \App\Models\InstagramAccount::create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'username' => $data['username'],
        'instagram_user_id' => rand(100000000, 999999999),
        'account_type' => $data['type'],
        'followers_count' => $data['followers'],
        'profile_picture_url' => 'https://i.pravatar.cc/150?img=' . rand(1, 70),
        'access_token' => encrypt('fake_token_' . time()),
        'token_expires_at' => now()->addDays(60),
        'status' => 'active',
        'ownership_type' => 'company',
        'is_shared' => false,
    ]);
    
    echo "✅ Created @{$account->username}\n";
}

exit
```

---

## 🧹 Clean Up Test Data

### Remove All Test Accounts

```bash
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'd6174173844@gmail.com')->first();
\$company = \$user->currentCompany;
\$count = \$company->instagramAccounts()->delete();
echo '✅ Removed ' . \$count . ' test accounts' . PHP_EOL;
echo '🔄 Refresh your browser!' . PHP_EOL;
"
```

### Keep Only One Account

```bash
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'd6174173844@gmail.com')->first();
\$company = \$user->currentCompany;
\$first = \$company->instagramAccounts()->first();
\$company->instagramAccounts()->where('id', '!=', \$first->id)->delete();
echo '✅ Kept only @' . \$first->username . PHP_EOL;
"
```

---

## ⚙️ Advanced: Account Settings

### Update Account Status

```php
$account = \App\Models\InstagramAccount::find(1);
$account->update(['status' => 'expired']);  // or 'active', 'error', 'disconnected'
```

### Update Followers Count

```php
$account = \App\Models\InstagramAccount::find(1);
$account->update(['followers_count' => 500000]);
```

### Extend Token Expiration

```php
$account = \App\Models\InstagramAccount::find(1);
$account->update(['token_expires_at' => now()->addDays(60)]);
```

### Change Ownership

```php
$account = \App\Models\InstagramAccount::find(1);
$account->update([
    'ownership_type' => 'user',  // or 'company'
    'is_shared' => true,
]);
```

---

## 📱 Using the UI

### View Accounts
1. Go to: `/instagram`
2. See all your connected accounts
3. View details: username, followers, status

### Disconnect Account
1. Click "Disconnect" button
2. Confirm the action
3. Account is removed

### Sync Account (Future Feature)
1. Click "Sync" button
2. Updates profile data from Instagram
3. **Note:** Only works with real Instagram credentials

---

## 🎯 Quick Reference Commands

### Add Account
```bash
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'd6174173844@gmail.com')->first();
\$account = \App\Models\InstagramAccount::create([
    'company_id' => \$user->currentCompany->id,
    'user_id' => \$user->id,
    'username' => 'new_account',
    'instagram_user_id' => rand(100000000, 999999999),
    'access_token' => encrypt('fake_' . time()),
    'token_expires_at' => now()->addDays(60),
    'account_type' => 'business',
    'profile_picture_url' => 'https://i.pravatar.cc/150?img=' . rand(1, 70),
    'followers_count' => rand(5000, 50000),
    'status' => 'active',
    'ownership_type' => 'company',
]);
echo '✅ Created @' . \$account->username;
"
```

### List Accounts
```bash
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'd6174173844@gmail.com')->first();
foreach (\$user->currentCompany->instagramAccounts as \$a) {
    echo 'ID: ' . \$a->id . ' | @' . \$a->username . PHP_EOL;
}
"
```

### Remove Account by ID
```bash
php artisan tinker --execute="
\App\Models\InstagramAccount::find(2)->delete();
echo '✅ Account removed';
"
```

### Remove All Accounts
```bash
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'd6174173844@gmail.com')->first();
\$count = \$user->currentCompany->instagramAccounts()->delete();
echo '✅ Removed ' . \$count . ' accounts';
"
```

---

## 💡 Pro Tips

1. **Profile Pictures:** Use `https://i.pravatar.cc/150?img=X` where X is 1-70 for variety
2. **Followers:** Use realistic numbers (1,000 - 500,000) for testing
3. **Usernames:** Use lowercase, underscores allowed, no spaces
4. **Status:** Keep 'active' for development testing
5. **Refresh Browser:** Always refresh after adding/removing accounts

---

## 🎉 Summary

**You now know how to:**
- ✅ Add new Instagram accounts (3 methods)
- ✅ Remove accounts (via UI or command)
- ✅ List all accounts
- ✅ Customize accounts (followers, type, status)
- ✅ Create bulk accounts
- ✅ Update account settings

**Current accounts:**
- ID: 1 | @dan_test_instagram
- ID: 2 | @my_second_instagram

**Refresh your browser to see the changes!** 🚀

---

**Last Updated:** October 10, 2025  
**Version:** 1.0  
**Status:** Complete Guide for Instagram Account Management

