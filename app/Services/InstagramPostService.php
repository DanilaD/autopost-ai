<?php

namespace App\Services;

use App\Enums\InstagramPostStatus;
use App\Models\InstagramAccount;
use App\Models\InstagramPost;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Instagram Post Service
 *
 * Handles creating, scheduling, and publishing posts to Instagram.
 * Manages the full post lifecycle from draft to published.
 */
class InstagramPostService
{
    protected InstagramAccountPermissionService $permissionService;

    public function __construct(InstagramAccountPermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    /**
     * Create a new draft post.
     *
     * @param  User  $user  The user creating the post
     * @param  InstagramAccount  $account  The account to post to
     * @param  array  $data  Post data (caption, media_urls, etc.)
     */
    public function createDraft(User $user, InstagramAccount $account, array $data): ?InstagramPost
    {
        // Check if user has permission to post to this account
        if (! $this->permissionService->canPost($user, $account)) {
            Log::warning('Unauthorized post creation attempt', [
                'user_id' => $user->id,
                'account_id' => $account->id,
            ]);

            return null;
        }

        try {
            $post = InstagramPost::create([
                'instagram_account_id' => $account->id,
                'user_id' => $user->id,
                'caption' => $data['caption'] ?? null,
                'media_type' => $data['media_type'] ?? 'image',
                'media_urls' => $data['media_urls'] ?? [],
                'status' => InstagramPostStatus::DRAFT,
                'metadata' => $data['metadata'] ?? [],
            ]);

            Log::info('Draft post created', [
                'post_id' => $post->id,
                'user_id' => $user->id,
                'account_id' => $account->id,
            ]);

            return $post;
        } catch (\Exception $e) {
            Log::error('Failed to create draft post', [
                'user_id' => $user->id,
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Update an existing draft post.
     */
    public function updateDraft(User $user, InstagramPost $post, array $data): bool
    {
        // Check if post is editable
        if (! $post->isEditable()) {
            Log::warning('Attempt to edit non-editable post', [
                'post_id' => $post->id,
                'status' => $post->status->value,
            ]);

            return false;
        }

        // Check if user owns the post or can manage the account
        if ($post->user_id !== $user->id && ! $this->permissionService->canManage($user, $post->instagramAccount)) {
            Log::warning('Unauthorized post edit attempt', [
                'user_id' => $user->id,
                'post_id' => $post->id,
            ]);

            return false;
        }

        try {
            $post->update([
                'caption' => $data['caption'] ?? $post->caption,
                'media_type' => $data['media_type'] ?? $post->media_type,
                'media_urls' => $data['media_urls'] ?? $post->media_urls,
                'metadata' => array_merge($post->metadata ?? [], $data['metadata'] ?? []),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update post', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Schedule a post for future publishing.
     */
    public function schedulePost(User $user, InstagramPost $post, \DateTime $scheduledAt): bool
    {
        // Validate scheduled time is in the future
        if ($scheduledAt <= now()) {
            Log::warning('Attempted to schedule post in the past', [
                'post_id' => $post->id,
                'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
            ]);

            return false;
        }

        // Check permissions
        if ($post->user_id !== $user->id && ! $this->permissionService->canManage($user, $post->instagramAccount)) {
            return false;
        }

        // Validate post has required data
        if (empty($post->media_urls)) {
            Log::warning('Attempted to schedule post without media', [
                'post_id' => $post->id,
            ]);

            return false;
        }

        try {
            $post->markAsScheduled($scheduledAt);

            Log::info('Post scheduled', [
                'post_id' => $post->id,
                'scheduled_at' => $scheduledAt->format('Y-m-d H:i:s'),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to schedule post', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Publish a post immediately to Instagram.
     *
     * Note: This is a placeholder for actual Instagram API integration.
     * You'll need to implement the actual media container creation and publishing
     * based on Instagram's Graph API documentation.
     */
    public function publishPost(InstagramPost $post): bool
    {
        $account = $post->instagramAccount;

        // Check if account is active and has valid token
        if (! $account->isActive() || $account->isTokenExpired()) {
            $post->markAsFailed('Instagram account is not active or token expired');

            return false;
        }

        try {
            $post->markAsPublishing();

            // TODO: Implement actual Instagram API publishing
            // For now, this is a placeholder that simulates publishing
            $result = $this->publishToInstagramApi($account, $post);

            if ($result['success']) {
                $post->markAsPublished(
                    $result['instagram_post_id'],
                    $result['permalink']
                );

                Log::info('Post published successfully', [
                    'post_id' => $post->id,
                    'instagram_post_id' => $result['instagram_post_id'],
                ]);

                return true;
            } else {
                $post->markAsFailed($result['error'] ?? 'Unknown error');

                return false;
            }
        } catch (\Exception $e) {
            $post->markAsFailed($e->getMessage());

            Log::error('Failed to publish post', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Cancel a scheduled post.
     */
    public function cancelPost(User $user, InstagramPost $post): bool
    {
        if (! $post->isCancellable()) {
            return false;
        }

        // Check permissions
        if ($post->user_id !== $user->id && ! $this->permissionService->canManage($user, $post->instagramAccount)) {
            return false;
        }

        try {
            $post->markAsCancelled();

            Log::info('Post cancelled', [
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to cancel post', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete a post.
     */
    public function deletePost(User $user, InstagramPost $post): bool
    {
        // Only owner or account manager can delete
        if ($post->user_id !== $user->id && ! $this->permissionService->canManage($user, $post->instagramAccount)) {
            return false;
        }

        // Can't delete published posts
        if ($post->status === InstagramPostStatus::PUBLISHED) {
            Log::warning('Attempted to delete published post', [
                'post_id' => $post->id,
            ]);

            return false;
        }

        try {
            $post->delete();

            Log::info('Post deleted', [
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete post', [
                'post_id' => $post->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get posts accessible by a user.
     */
    public function getPostsForUser(User $user, array $filters = [])
    {
        $query = InstagramPost::query()
            ->whereHas('instagramAccount', function ($q) use ($user) {
                $q->accessibleBy($user);
            })
            ->with(['instagramAccount', 'user']);

        // Apply filters
        if (isset($filters['status'])) {
            $query->withStatus($filters['status']);
        }

        if (isset($filters['account_id'])) {
            $query->where('instagram_account_id', $filters['account_id']);
        }

        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->latest()->get();
    }

    /**
     * Get posts due for publishing (scheduled posts that should be published now).
     */
    public function getDuePostsForPublishing()
    {
        return InstagramPost::dueForPublishing()
            ->with(['instagramAccount', 'user'])
            ->get();
    }

    /**
     * Placeholder for actual Instagram API publishing.
     *
     * This should be implemented based on Instagram Graph API documentation:
     * https://developers.facebook.com/docs/instagram-api/guides/content-publishing
     *
     * The basic flow is:
     * 1. Upload media to Instagram
     * 2. Create a media container
     * 3. Publish the container
     *
     * @return array{success: bool, instagram_post_id?: string, permalink?: string, error?: string}
     */
    private function publishToInstagramApi(InstagramAccount $account, InstagramPost $post): array
    {
        // TODO: Implement actual Instagram API integration
        // This is a placeholder that simulates success

        // For now, just log that we would publish
        Log::info('Would publish to Instagram API', [
            'account_id' => $account->id,
            'post_id' => $post->id,
            'caption' => $post->caption,
            'media_urls' => $post->media_urls,
        ]);

        // Simulate success (in production, this would be real Instagram API response)
        return [
            'success' => true,
            'instagram_post_id' => 'ig_'.uniqid(),
            'permalink' => 'https://www.instagram.com/p/'.uniqid(),
        ];

        /*
        // Real implementation would look something like this:

        try {
            // Step 1: Upload media and create container
            $containerId = $this->createMediaContainer($account, $post);

            // Step 2: Publish the container
            $response = Http::post("https://graph.instagram.com/v18.0/{$account->instagram_user_id}/media_publish", [
                'creation_id' => $containerId,
                'access_token' => $account->access_token,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'instagram_post_id' => $data['id'],
                    'permalink' => $this->getPostPermalink($account, $data['id']),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Unknown error',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
        */
    }
}
