<?php

namespace App\Enums;

/**
 * Post Type Enum
 *
 * Represents the type of Instagram post.
 */
enum PostType: string
{
    case FEED = 'feed';
    case REEL = 'reel';
    case STORY = 'story';
    case CAROUSEL = 'carousel';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::FEED => __('posts.type.feed'),
            self::REEL => __('posts.type.reel'),
            self::STORY => __('posts.type.story'),
            self::CAROUSEL => __('posts.type.carousel'),
        };
    }

    /**
     * Get description
     */
    public function description(): string
    {
        return match ($this) {
            self::FEED => __('posts.type.feed_description'),
            self::REEL => __('posts.type.reel_description'),
            self::STORY => __('posts.type.story_description'),
            self::CAROUSEL => __('posts.type.carousel_description'),
        };
    }

    /**
     * Get maximum media count for this type
     */
    public function maxMediaCount(): int
    {
        return match ($this) {
            self::FEED => 1,
            self::REEL => 1,
            self::STORY => 1,
            self::CAROUSEL => 10,
        };
    }

    /**
     * Get allowed media types for this post type
     */
    public function allowedMediaTypes(): array
    {
        return match ($this) {
            self::FEED => ['image', 'video'],
            self::REEL => ['video'],
            self::STORY => ['image', 'video'],
            self::CAROUSEL => ['image', 'video'],
        };
    }
}
