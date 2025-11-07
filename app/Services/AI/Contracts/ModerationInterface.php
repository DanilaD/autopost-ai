<?php

namespace App\Services\AI\Contracts;

/**
 * Interface for content moderation capabilities
 *
 * This interface defines the contract for AI providers that can moderate content,
 * checking for inappropriate, harmful, or policy-violating content.
 *
 * @version 1.0
 *
 * @since 2025-10-16
 */
interface ModerationInterface
{
    /**
     * Moderate text content for policy violations
     *
     * @param  string  $content  The text content to moderate
     * @param  array  $options  Additional options (strict_mode, categories, etc.)
     * @return array Response containing moderation results and flags
     */
    public function moderateText(string $content, array $options = []): array;

    /**
     * Moderate image content for policy violations
     *
     * @param  string  $imagePath  Path to the image to moderate
     * @param  array  $options  Additional options
     * @return array Response containing moderation results and flags
     */
    public function moderateImage(string $imagePath, array $options = []): array;

    /**
     * Check if content is safe for publication
     *
     * @param  string  $content  The content to check
     * @param  string  $type  Type of content (text, image, video)
     * @param  array  $options  Additional options
     * @return bool True if content is safe, false if it violates policies
     */
    public function isContentSafe(string $content, string $type = 'text', array $options = []): bool;

    /**
     * Get detailed moderation analysis
     *
     * @param  string  $content  The content to analyze
     * @param  string  $type  Type of content
     * @param  array  $options  Additional options
     * @return array Detailed analysis with categories and confidence scores
     */
    public function getModerationAnalysis(string $content, string $type = 'text', array $options = []): array;

    /**
     * Check if moderation is supported by this provider
     *
     * @return bool True if moderation is supported, false otherwise
     */
    public function supportsModeration(): bool;
}
