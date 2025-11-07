<?php

namespace App\Enums;

/**
 * Enum for AI model types
 *
 * This enum defines the different types of AI models available,
 * including text, image, and video generation models.
 *
 * @version 1.0
 *
 * @since 2025-10-16
 */
enum AIModelType: string
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case VIDEO = 'video';
    case MULTIMODAL = 'multimodal';

    /**
     * Get the display name for the model type
     *
     * @return string Human-readable type name
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::TEXT => 'Text Generation',
            self::IMAGE => 'Image Generation',
            self::VIDEO => 'Video Generation',
            self::MULTIMODAL => 'Multimodal',
        };
    }

    /**
     * Get the description for the model type
     *
     * @return string Type description
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::TEXT => 'Models that generate text content',
            self::IMAGE => 'Models that generate images from text prompts',
            self::VIDEO => 'Models that generate video content',
            self::MULTIMODAL => 'Models that can process multiple input types',
        };
    }

    /**
     * Check if this model type supports text input
     *
     * @return bool True if text input is supported
     */
    public function supportsTextInput(): bool
    {
        return true; // All model types support text input
    }

    /**
     * Check if this model type supports image input
     *
     * @return bool True if image input is supported
     */
    public function supportsImageInput(): bool
    {
        return match ($this) {
            self::TEXT => false,
            self::IMAGE => true,
            self::VIDEO => true,
            self::MULTIMODAL => true,
        };
    }

    /**
     * Check if this model type supports video input
     *
     * @return bool True if video input is supported
     */
    public function supportsVideoInput(): bool
    {
        return match ($this) {
            self::TEXT => false,
            self::IMAGE => false,
            self::VIDEO => true,
            self::MULTIMODAL => true,
        };
    }

    /**
     * Get the default pricing tier for this model type
     *
     * @return string Pricing tier (low, medium, high)
     */
    public function getPricingTier(): string
    {
        return match ($this) {
            self::TEXT => 'low',
            self::IMAGE => 'medium',
            self::VIDEO => 'high',
            self::MULTIMODAL => 'medium',
        };
    }

    /**
     * Get all model type values as array
     *
     * @return array Array of model type values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
