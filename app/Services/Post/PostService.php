<?php

namespace App\Services\Post;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Post;
use App\Repositories\Post\PostMediaRepository;
use App\Repositories\Post\PostRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Post Service
 *
 * Handles all business logic related to posts.
 */
class PostService
{
    /**
     * Constructor dependency injection
     */
    public function __construct(
        private PostRepository $postRepository,
        private PostMediaRepository $mediaRepository
    ) {}

    /**
     * Get all posts for a company
     *
     * @return \Illuminate\Support\Collection
     */
    public function getCompanyPosts(int $companyId, array $filters = [])
    {
        Log::info('Fetching posts for company', ['company_id' => $companyId]);

        return $this->postRepository->getByCompany($companyId, $filters);
    }

    /**
     * Get all posts for an individual user
     *
     * @return \Illuminate\Support\Collection
     */
    public function getByUser(int $userId, array $filters = [])
    {
        Log::info('Fetching posts for user', ['user_id' => $userId]);

        return $this->postRepository->getByUser($userId, $filters);
    }

    /**
     * Create a new post
     *
     * Business rules:
     * - User must belong to company
     * - Caption max 2200 characters
     * - Scheduled time must be in future
     * - Media count must match post type requirements
     *
     * @throws \Exception
     */
    public function createPost(int $companyId, array $data): Post
    {
        // Validate business rules
        $this->validatePostData($data);

        return DB::transaction(function () use ($companyId, $data) {
            // Determine defaults for optional fields
            $accountId = $data['instagram_account_id'] ?? optional(auth()->user()->currentCompany?->instagramAccounts()->first())->id;
            // Allow posts without Instagram accounts - they can be assigned later
            $resolvedType = $data['type'] ?? PostType::FEED;

            // Handle scheduled_at conversion to UTC
            $scheduledAt = null;
            if (! empty($data['scheduled_at'])) {
                // Parse the ISO string and ensure it's treated as UTC
                $scheduledAt = \Carbon\Carbon::parse($data['scheduled_at'])->utc();
            }

            // Create post
            $post = $this->postRepository->create([
                'company_id' => $companyId,
                'created_by' => auth()->id(),
                'instagram_account_id' => $accountId, // Can be null
                'type' => $resolvedType,
                'title' => $data['title'] ?? null,
                'caption' => $data['caption'] ?? null,
                'hashtags' => $this->extractHashtags($data['caption'] ?? ''),
                'mentions' => $this->extractMentions($data['caption'] ?? ''),
                'scheduled_at' => $scheduledAt,
                'status' => ! empty($data['scheduled_at']) ? PostStatus::SCHEDULED : PostStatus::DRAFT,
                'metadata' => $data['metadata'] ?? [],
            ]);

            // Create media if provided
            if (! empty($data['media'])) {
                $this->createPostMedia($post, $data['media']);
            }

            Log::info('Post created successfully', ['post_id' => $post->id]);

            return $post->load('media');
        });
    }

    /**
     * Update an existing post
     *
     * @throws \Exception
     */
    public function updatePost(Post $post, array $data): Post
    {
        if (! $post->canBeEdited()) {
            throw new \InvalidArgumentException(__('posts.cannot_edit_published'));
        }

        $this->validatePostData($data, $post);

        return DB::transaction(function () use ($post, $data) {
            // Handle scheduled_at conversion to UTC
            $scheduledAt = $post->scheduled_at;
            if (isset($data['scheduled_at'])) {
                if (! empty($data['scheduled_at'])) {
                    // Parse the ISO string and ensure it's treated as UTC
                    $scheduledAt = \Carbon\Carbon::parse($data['scheduled_at'])->utc();
                } else {
                    $scheduledAt = null;
                }
            }

            // Update post
            $this->postRepository->update($post, [
                'type' => $data['type'] ?? $post->type,
                'title' => $data['title'] ?? $post->title,
                'caption' => $data['caption'] ?? $post->caption,
                'hashtags' => $this->extractHashtags($data['caption'] ?? $post->caption ?? ''),
                'mentions' => $this->extractMentions($data['caption'] ?? $post->caption ?? ''),
                'scheduled_at' => $scheduledAt,
                'status' => ! empty($scheduledAt) ? PostStatus::SCHEDULED : PostStatus::DRAFT,
                'metadata' => array_merge($post->metadata ?? [], $data['metadata'] ?? []),
            ]);

            // Update media if provided
            if (isset($data['media'])) {
                $this->updatePostMedia($post, $data['media']);
            }

            Log::info('Post updated successfully', ['post_id' => $post->id]);

            return $post->fresh(['media']);
        });
    }

    /**
     * Delete a post
     *
     * @throws \Exception
     */
    public function deletePost(Post $post): bool
    {
        if (! $post->canBeEdited()) {
            throw new \InvalidArgumentException(__('posts.cannot_delete_published'));
        }

        return DB::transaction(function () use ($post) {
            // Ensure media is loaded to avoid N+1 queries
            $post->load('media');

            // Delete media files
            foreach ($post->media as $media) {
                Storage::delete($media->storage_path);
            }

            // Delete post (media will be deleted by cascade)
            $deleted = $this->postRepository->delete($post);

            Log::info('Post deleted successfully', ['post_id' => $post->id]);

            return $deleted;
        });
    }

    /**
     * Schedule a post for publishing
     *
     * @throws \Exception
     */
    public function schedulePost(Post $post, \DateTime $scheduledAt): Post
    {
        if (! $post->canBeEdited()) {
            throw new \InvalidArgumentException(__('posts.cannot_schedule_published'));
        }

        if ($scheduledAt <= now()) {
            throw new \InvalidArgumentException(__('posts.scheduled_time_must_be_future'));
        }

        // Ensure media is loaded to avoid N+1 queries
        $post->load('media');

        if ($post->media->isEmpty()) {
            throw new \InvalidArgumentException(__('posts.media_required_for_scheduling'));
        }

        $this->postRepository->update($post, [
            'scheduled_at' => $scheduledAt,
            'status' => PostStatus::SCHEDULED,
        ]);

        Log::info('Post scheduled successfully', [
            'post_id' => $post->id,
            'scheduled_at' => $scheduledAt,
        ]);

        return $post->fresh();
    }

    /**
     * Get posts due for publishing
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPostsDueForPublishing()
    {
        return $this->postRepository->getDueForPublishing();
    }

    /**
     * Get post statistics for a company
     */
    public function getStats(int $companyId): array
    {
        return $this->postRepository->getStats($companyId);
    }

    /**
     * Get post statistics for an individual user
     */
    public function getStatsByUser(int $userId): array
    {
        // Use raw SQL for better performance instead of loading all posts
        $stats = $this->postRepository->getStatsByUser($userId);

        return [
            'total' => $stats['total'] ?? 0,
            'drafts' => $stats['drafts'] ?? 0,
            'scheduled' => $stats['scheduled'] ?? 0,
            'publishing' => $stats['publishing'] ?? 0,
            'published' => $stats['published'] ?? 0,
            'failed' => $stats['failed'] ?? 0,
        ];
    }

    /**
     * Validate post data against business rules
     */
    private function validatePostData(array $data, ?Post $existingPost = null): void
    {
        $postType = PostType::from($data['type'] ?? $existingPost?->type->value);

        // Validate caption length
        $caption = $data['caption'] ?? $existingPost?->caption ?? '';
        if (strlen($caption) > 2200) {
            throw new \InvalidArgumentException(__('posts.caption_too_long'));
        }

        // Validate scheduled time
        if (! empty($data['scheduled_at']) && strtotime($data['scheduled_at']) <= time()) {
            throw new \InvalidArgumentException(__('posts.scheduled_time_must_be_future'));
        }

        // Validate media count
        $mediaCount = count($data['media'] ?? $existingPost?->media ?? []);
        if ($mediaCount > $postType->maxMediaCount()) {
            throw new \InvalidArgumentException(__('posts.too_many_media', [
                'max' => $postType->maxMediaCount(),
                'type' => $postType->label(),
            ]));
        }

        // Validate media types
        if (! empty($data['media'])) {
            $allowedTypes = $postType->allowedMediaTypes();
            foreach ($data['media'] as $media) {
                if (! in_array($media['type'], $allowedTypes)) {
                    throw new \InvalidArgumentException(__('posts.invalid_media_type', [
                        'type' => $media['type'],
                        'post_type' => $postType->label(),
                    ]));
                }
            }
        }
    }

    /**
     * Create media for a post
     */
    private function createPostMedia(Post $post, array $mediaData): void
    {
        foreach ($mediaData as $index => $media) {
            $this->mediaRepository->create([
                'post_id' => $post->id,
                'type' => $media['type'],
                'filename' => $media['filename'],
                'original_filename' => $media['original_filename'],
                'mime_type' => $media['mime_type'],
                'file_size' => $media['file_size'],
                'storage_path' => $media['storage_path'],
                'url' => $media['url'] ?? null,
                'order' => $index,
                'metadata' => $media['metadata'] ?? [],
            ]);
        }
    }

    /**
     * Update media for a post
     */
    private function updatePostMedia(Post $post, array $mediaData): void
    {
        // Ensure media is loaded to avoid N+1 queries
        $post->load('media');

        // Delete existing media
        foreach ($post->media as $media) {
            Storage::delete($media->storage_path);
            $this->mediaRepository->delete($media);
        }

        // Create new media
        $this->createPostMedia($post, $mediaData);
    }

    /**
     * Extract hashtags from caption
     */
    private function extractHashtags(string $caption): array
    {
        preg_match_all('/#(\w+)/', $caption, $matches);

        return array_unique($matches[1] ?? []);
    }

    /**
     * Extract mentions from caption
     */
    private function extractMentions(string $caption): array
    {
        preg_match_all('/@(\w+)/', $caption, $matches);

        return array_unique($matches[1] ?? []);
    }
}
