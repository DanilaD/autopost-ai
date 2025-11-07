<?php

namespace App\Enums;

/**
 * Enum for AI generation types
 *
 * This enum defines the different types of content that can be generated
 * by the AI system, including text, images, videos, and content plans.
 *
 * @version 1.0
 *
 * @since 2025-10-16
 */
enum AIGenerationType: string
{
    case CAPTION = 'caption';
    case IMAGE = 'image';
    case VIDEO = 'video';
    case PLAN = 'plan';
    case HASHTAGS = 'hashtags';
    case DESCRIPTION = 'description';
    case CHAT = 'chat';

    /**
     * Get the display name for the generation type
     *
     * @return string Human-readable type name
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::CAPTION => 'Caption',
            self::IMAGE => 'Image',
            self::VIDEO => 'Video',
            self::PLAN => 'Content Plan',
            self::HASHTAGS => 'Hashtags',
            self::DESCRIPTION => 'Description',
            self::CHAT => 'Chat Response',
        };
    }

    /**
     * Get the description for the generation type
     *
     * @return string Type description
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::CAPTION => 'Social media captions and posts',
            self::IMAGE => 'AI-generated images and artwork',
            self::VIDEO => 'AI-generated video content',
            self::PLAN => 'Content planning and strategy',
            self::HASHTAGS => 'Relevant hashtags for content',
            self::DESCRIPTION => 'Product and content descriptions',
            self::CHAT => 'Interactive chat responses',
        };
    }

    /**
     * Check if this type requires text generation
     *
     * @return bool True if text generation is required
     */
    public function requiresTextGeneration(): bool
    {
        return match ($this) {
            self::CAPTION => true,
            self::IMAGE => false,
            self::VIDEO => false,
            self::PLAN => true,
            self::HASHTAGS => true,
            self::DESCRIPTION => true,
            self::CHAT => true,
        };
    }

    /**
     * Check if this type requires image generation
     *
     * @return bool True if image generation is required
     */
    public function requiresImageGeneration(): bool
    {
        return match ($this) {
            self::CAPTION => false,
            self::IMAGE => true,
            self::VIDEO => false,
            self::PLAN => false,
            self::HASHTAGS => false,
            self::DESCRIPTION => false,
            self::CHAT => false,
        };
    }

    /**
     * Get the default cost multiplier for this type
     *
     * @return float Cost multiplier
     */
    public function getCostMultiplier(): float
    {
        return match ($this) {
            self::CAPTION => 1.0,
            self::IMAGE => 10.0, // Images are typically more expensive
            self::VIDEO => 50.0, // Videos are the most expensive
            self::PLAN => 2.0,   // Plans require more tokens
            self::HASHTAGS => 0.5, // Hashtags are simple
            self::DESCRIPTION => 1.5,
            self::CHAT => 1.0,
        };
    }

    /**
     * Get all generation type values as array
     *
     * @return array Array of generation type values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
