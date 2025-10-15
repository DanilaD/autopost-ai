# Instagram Hybrid Ownership Model - Implementation Guide

## Overview

This document describes the **Hybrid Ownership Model** for Instagram accounts in the AutoPost AI platform. This architecture allows both users and companies to own Instagram accounts, with fine-grained permission controls for sharing and collaboration.

## Architecture

### Ownership Types

Instagram accounts can now have two types of ownership:

1. **User-Owned Accounts** (`ownership_type = 'user'`)
   - Personal Instagram accounts owned by individual users
   - Users have full control over their accounts
   - Can be shared with other users or teams
   
2. **Company-Owned Accounts** (`ownership_type = 'company'`)
   - Instagram accounts owned by a company/team
   - All company members have access
   - Admins have full management rights

## Database Schema

### Modified Tables

#### `instagram_accounts`
```sql
- company_id (nullable) - Foreign key to companies table
+ user_id (nullable) - Foreign key to users table (for user-owned accounts)
+ is_shared (boolean) - Whether account is shared with others
+ ownership_type (enum: 'user', 'company') - Explicit ownership type
```

### New Tables

#### `instagram_account_user` (Pivot Table)
Manages sharing permissions between users and Instagram accounts.

```sql
- instagram_account_id (foreign key)
- user_id (foreign key)
- can_post (boolean) - User can create/publish posts
- can_manage (boolean) - User can modify settings, reconnect, disconnect
- shared_at (timestamp) - When access was granted
- shared_by_user_id (foreign key, nullable) - Who shared the account
```

#### `instagram_posts`
Tracks all posts created through the platform.

```sql
- instagram_account_id (foreign key) - Which account to post to
- user_id (foreign key) - Who created the post
- caption (text)
- media_type (enum: 'image', 'video', 'carousel')
- media_urls (json) - Array of media file paths
- instagram_post_id (string) - Instagram's ID after publishing
- instagram_permalink (string) - Link to published post
- scheduled_at (timestamp) - When to publish
- published_at (timestamp) - When it was published
- status (enum) - draft, scheduled, publishing, published, failed, cancelled
- error_message (text) - Error details if failed
- retry_count (integer) - Number of retry attempts
- metadata (json) - Additional data
```

## Models

### InstagramAccount Model

#### New Relationships
```php
// Get the user owner (for user-owned accounts)
$account->owner; // User

// Get users who have shared access
$account->sharedWithUsers; // Collection of Users with pivot data

// Get all posts made through this account
$account->posts; // Collection of InstagramPost
```

#### New Scopes
```php
// Filter by ownership type
InstagramAccount::userOwned()->get();
InstagramAccount::companyOwned()->get();

// Get accounts owned by a specific user
InstagramAccount::ownedBy($user)->get();

// Get all accounts accessible by a user
InstagramAccount::accessibleBy($user)->get();
```

#### Permission Methods
```php
// Check ownership
$account->isUserOwned(); // boolean
$account->isCompanyOwned(); // boolean
$account->isOwnedBy($user); // boolean

// Check access
$account->isAccessibleBy($user); // boolean
$account->canUserPost($user); // boolean
$account->canUserManage($user); // boolean

// Manage sharing
$account->shareWith($user, canPost: true, canManage: false, sharedBy: $currentUser);
$account->revokeAccessFor($user);

// Display name with context
$account->display_name; // "@username (Owner Name)" or "@username (Company Name)"
```

### User Model

#### New Relationships
```php
// Get Instagram accounts owned by the user
$user->ownedInstagramAccounts; // HasMany

// Get Instagram accounts shared with the user
$user->sharedInstagramAccounts; // BelongsToMany with pivot

// Get all accessible accounts (owned + company + shared)
$user->accessibleInstagramAccounts(); // Query builder

// Get posts created by the user
$user->instagramPosts; // HasMany
```

#### Helper Methods
```php
// Check if user has any Instagram accounts
$user->hasInstagramAccounts(); // boolean

// Get the default account to use
$user->getDefaultInstagramAccount(); // ?InstagramAccount
```

### InstagramPost Model

#### Status Enum
```php
InstagramPostStatus::DRAFT
InstagramPostStatus::SCHEDULED
InstagramPostStatus::PUBLISHING
InstagramPostStatus::PUBLISHED
InstagramPostStatus::FAILED
InstagramPostStatus::CANCELLED
```

#### Scopes
```php
InstagramPost::drafts()->get();
InstagramPost::scheduled()->get();
InstagramPost::published()->get();
InstagramPost::failed()->get();
InstagramPost::dueForPublishing()->get(); // Scheduled posts ready to publish
```

#### Methods
```php
$post->isEditable(); // boolean
$post->isCancellable(); // boolean
$post->isFinal(); // boolean
$post->canRetry(); // boolean

$post->markAsScheduled($dateTime);
$post->markAsPublishing();
$post->markAsPublished($instagramPostId, $permalink);
$post->markAsFailed($errorMessage);
$post->markAsCancelled();
$post->retry();
```

## Services

### InstagramService

Updated to support both user and company account connections.

```php
use App\Services\InstagramService;

$service = app(InstagramService::class);

// Connect account to a company
$account = $service->connectAccountForCompany($company, $authorizationCode);

// Connect account to a user
$account = $service->connectAccountForUser($user, $authorizationCode);

// Legacy method (still works, connects to company)
$account = $service->connectAccount($company, $authorizationCode);
```

### InstagramAccountPermissionService

Centralized permission checking and management.

```php
use App\Services\InstagramAccountPermissionService;

$permissionService = app(InstagramAccountPermissionService::class);

// Check permissions
$permissionService->canView($user, $account); // boolean
$permissionService->canPost($user, $account); // boolean
$permissionService->canManage($user, $account); // boolean
$permissionService->canShare($user, $account); // boolean
$permissionService->canDelete($user, $account); // boolean

// Get all accessible accounts with permission details
$accounts = $permissionService->getAccessibleAccountsWithPermissions($user);
// Returns: [
//   'account' => InstagramAccount,
//   'permissions' => ['can_view' => true, 'can_post' => true, ...],
//   'access_type' => 'owner|company|shared'
// ]

// Get access type
$type = $permissionService->getAccessType($user, $account); // 'owner', 'company', 'shared', or 'none'

// Share account
$permissionService->shareAccount(
    $account,
    $targetUser,
    $sharingUser,
    canPost: true,
    canManage: false
);

// Revoke access
$permissionService->revokeAccess($account, $targetUser, $revokingUser);

// Authorize (throws exception if unauthorized)
$permissionService->authorize($user, $account, 'manage');
```

### InstagramPostService

Manages the full post lifecycle.

```php
use App\Services\InstagramPostService;

$postService = app(InstagramPostService::class);

// Create draft
$post = $postService->createDraft($user, $account, [
    'caption' => 'My amazing post!',
    'media_type' => 'image',
    'media_urls' => ['path/to/image.jpg'],
    'metadata' => ['source' => 'mobile_app'],
]);

// Update draft
$postService->updateDraft($user, $post, [
    'caption' => 'Updated caption',
]);

// Schedule for later
$postService->schedulePost($user, $post, now()->addHours(2));

// Publish immediately
$postService->publishPost($post);

// Cancel scheduled post
$postService->cancelPost($user, $post);

// Delete post
$postService->deletePost($user, $post);

// Get posts for a user
$posts = $postService->getPostsForUser($user, [
    'status' => InstagramPostStatus::SCHEDULED,
    'account_id' => $account->id,
]);

// Get posts ready to publish (for queue worker)
$duePosts = $postService->getDuePostsForPublishing();
```

## Permission Matrix

| Action | Owner | Company Admin | Company Member | Shared (Post) | Shared (Manage) | Stranger |
|--------|-------|---------------|----------------|---------------|-----------------|----------|
| View Account | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Post Content | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ |
| Manage Settings | ✅ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Share Account | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |
| Delete Account | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ |

## Usage Examples

### Example 1: User Connects Personal Account

```php
// In your controller
public function connectInstagram(Request $request)
{
    $user = auth()->user();
    $code = $request->input('code');
    
    $instagramService = app(InstagramService::class);
    $account = $instagramService->connectAccountForUser($user, $code);
    
    if ($account) {
        return redirect()->route('dashboard')
            ->with('success', 'Instagram account connected successfully!');
    }
    
    return back()->with('error', 'Failed to connect Instagram account.');
}
```

### Example 2: Share Account with Team Member

```php
public function shareAccount(Request $request, InstagramAccount $account)
{
    $user = auth()->user();
    $targetUser = User::findOrFail($request->input('user_id'));
    
    $permissionService = app(InstagramAccountPermissionService::class);
    
    $shared = $permissionService->shareAccount(
        $account,
        $targetUser,
        $user,
        canPost: $request->boolean('can_post'),
        canManage: $request->boolean('can_manage')
    );
    
    if ($shared) {
        return response()->json(['message' => 'Account shared successfully']);
    }
    
    return response()->json(['message' => 'Failed to share account'], 403);
}
```

### Example 3: Create and Schedule Post

```php
public function createPost(Request $request)
{
    $user = auth()->user();
    $account = InstagramAccount::findOrFail($request->input('account_id'));
    
    $postService = app(InstagramPostService::class);
    
    // Create draft
    $post = $postService->createDraft($user, $account, [
        'caption' => $request->input('caption'),
        'media_urls' => $request->input('media_urls'),
        'media_type' => 'image',
    ]);
    
    if (!$post) {
        return back()->with('error', 'You do not have permission to post to this account');
    }
    
    // Schedule if requested
    if ($request->has('schedule_at')) {
        $scheduledAt = new DateTime($request->input('schedule_at'));
        $postService->schedulePost($user, $post, $scheduledAt);
    }
    
    return redirect()->route('posts.index')
        ->with('success', 'Post created successfully!');
}
```

### Example 4: Get All Accessible Accounts for User

```php
public function myAccounts()
{
    $user = auth()->user();
    $permissionService = app(InstagramAccountPermissionService::class);
    
    $accountsWithPermissions = $permissionService->getAccessibleAccountsWithPermissions($user);
    
    return view('instagram.accounts', [
        'accounts' => $accountsWithPermissions,
    ]);
}
```

```blade
{{-- In Blade template --}}
@foreach($accounts as $item)
    <div class="account-card">
        <h3>{{ $item['account']->display_name }}</h3>
        <span class="badge">{{ $item['access_type'] }}</span>
        
        @if($item['permissions']['can_post'])
            <a href="{{ route('posts.create', ['account' => $item['account']->id]) }}">
                Create Post
            </a>
        @endif
        
        @if($item['permissions']['can_manage'])
            <a href="{{ route('accounts.settings', $item['account']) }}">
                Settings
            </a>
        @endif
        
        @if($item['permissions']['can_share'])
            <a href="{{ route('accounts.share', $item['account']) }}">
                Share
            </a>
        @endif
    </div>
@endforeach
```

## Testing

### Running Tests

```bash
# Run all Instagram-related tests
php artisan test --filter Instagram

# Run specific test suites
php artisan test tests/Feature/InstagramAccountOwnershipTest.php
php artisan test tests/Feature/InstagramAccountPermissionTest.php
php artisan test tests/Feature/InstagramPostManagementTest.php
```

### Using Factories in Tests

```php
use App\Models\InstagramAccount;
use App\Models\InstagramPost;

// Create user-owned account
$account = InstagramAccount::factory()
    ->forUser($user)
    ->create();

// Create company-owned account
$account = InstagramAccount::factory()
    ->forCompany($company)
    ->business()
    ->create();

// Create shared account
$account = InstagramAccount::factory()
    ->forUser($user)
    ->shared()
    ->create();

// Create scheduled post
$post = InstagramPost::factory()
    ->forAccount($account)
    ->byUser($user)
    ->scheduled(now()->addHours(2))
    ->create();

// Create published post
$post = InstagramPost::factory()
    ->forAccount($account)
    ->published()
    ->create();
```

## Migration

### Running Migrations

```bash
# Run the new migrations
php artisan migrate

# If you need to roll back
php artisan migrate:rollback --step=3
```

### Data Migration Notes

All existing Instagram accounts will be automatically marked as `ownership_type = 'company'` during migration. No manual data migration is needed.

## Next Steps

1. **Implement UI Components**
   - Account selector dropdown
   - Permission management interface
   - Sharing modal

2. **Add Queue Workers**
   - Process scheduled posts
   - Retry failed posts
   - Refresh expiring tokens

3. **Add Notifications**
   - Notify users when account is shared with them
   - Alert on failed posts
   - Warn about expiring tokens

4. **Implement Actual Instagram API**
   - The `InstagramPostService::publishToInstagramApi()` method is currently a placeholder
   - Implement real Instagram Graph API calls for publishing
   - Handle media uploads and container creation

5. **Add Analytics**
   - Track post performance
   - Monitor account engagement
   - Report on scheduling efficiency

## Troubleshooting

### Common Issues

**Issue: User can't see company accounts**
- Verify user is a member of the company: `$company->hasMember($user)`
- Check user's current_company_id is set correctly

**Issue: Permission denied when posting**
- Use `InstagramAccountPermissionService::canPost()` to check
- Verify account status is 'active'
- Check token hasn't expired

**Issue: Shared account not showing up**
- Verify relationship exists in `instagram_account_user` table
- Check `is_shared` flag is true on the account
- Use `$user->sharedInstagramAccounts` to debug

## Security Considerations

1. **Access Tokens**: All Instagram access tokens are encrypted using Laravel's `Crypt` facade
2. **Permission Checks**: Always use `InstagramAccountPermissionService` before performing actions
3. **Audit Trail**: All posts track which user created them
4. **Soft Deletes**: Posts are soft-deleted to maintain audit history

## API Endpoints (TODO)

Future API endpoints to implement:

```
GET    /api/instagram/accounts              - List accessible accounts
POST   /api/instagram/accounts/connect      - Connect new account
DELETE /api/instagram/accounts/{id}         - Disconnect account
POST   /api/instagram/accounts/{id}/share   - Share account
DELETE /api/instagram/accounts/{id}/share/{user} - Revoke access

GET    /api/instagram/posts                 - List posts
POST   /api/instagram/posts                 - Create post
PUT    /api/instagram/posts/{id}            - Update post
DELETE /api/instagram/posts/{id}            - Delete post
POST   /api/instagram/posts/{id}/schedule   - Schedule post
POST   /api/instagram/posts/{id}/publish    - Publish immediately
POST   /api/instagram/posts/{id}/cancel     - Cancel scheduled post
```

---

**Author**: Senior Developer  
**Date**: October 10, 2025  
**Version**: 1.0.0

