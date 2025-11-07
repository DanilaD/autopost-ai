<?php

namespace App\Services\AI\Contracts;

/**
 * Interface for image generation capabilities
 *
 * This interface defines the contract for AI providers that can generate images,
 * including DALL-E, Imagen, and other image generation models.
 *
 * @version 1.0
 *
 * @since 2025-10-16
 */
interface ImageGenerationInterface
{
    /**
     * Generate an image based on a text prompt
     *
     * @param  string  $prompt  The text description of the desired image
     * @param  array  $options  Additional options (size, quality, style, etc.)
     * @return array Response containing image URL/data and metadata
     */
    public function generateImage(string $prompt, array $options = []): array;

    /**
     * Generate multiple images based on a text prompt
     *
     * @param  string  $prompt  The text description of the desired image
     * @param  int  $count  Number of images to generate
     * @param  array  $options  Additional options
     * @return array Response containing image URLs/data and metadata
     */
    public function generateImages(string $prompt, int $count = 1, array $options = []): array;

    /**
     * Edit an existing image based on a prompt
     *
     * @param  string  $imagePath  Path to the image to edit
     * @param  string  $prompt  Description of the desired changes
     * @param  array  $options  Additional options
     * @return array Response containing edited image URL/data and metadata
     */
    public function editImage(string $imagePath, string $prompt, array $options = []): array;

    /**
     * Generate image variations
     *
     * @param  string  $imagePath  Path to the base image
     * @param  int  $count  Number of variations to generate
     * @param  array  $options  Additional options
     * @return array Response containing variation URLs/data and metadata
     */
    public function generateVariations(string $imagePath, int $count = 1, array $options = []): array;

    /**
     * Check if image generation is supported by this provider
     *
     * @return bool True if image generation is supported, false otherwise
     */
    public function supportsImageGeneration(): bool;
}
