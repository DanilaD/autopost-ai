<?php

namespace App\Enums;

/**
 * Post Status Enum
 *
 * Represents the lifecycle status of a post.
 */
enum PostStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PUBLISHING = 'publishing';
    case PUBLISHED = 'published';
    case FAILED = 'failed';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => __('posts.status.draft'),
            self::SCHEDULED => __('posts.status.scheduled'),
            self::PUBLISHING => __('posts.status.publishing'),
            self::PUBLISHED => __('posts.status.published'),
            self::FAILED => __('posts.status.failed'),
        };
    }

    /**
     * Get color for UI display
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SCHEDULED => 'blue',
            self::PUBLISHING => 'yellow',
            self::PUBLISHED => 'green',
            self::FAILED => 'red',
        };
    }

    /**
     * Check if post can be edited
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::SCHEDULED, self::FAILED]);
    }

    /**
     * Check if post is ready to publish
     */
    public function isReadyToPublish(): bool
    {
        return $this === self::SCHEDULED;
    }
}
