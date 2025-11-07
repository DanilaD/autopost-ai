<?php

namespace App\Services\AI\Contracts;

/**
 * Interface for text generation capabilities
 *
 * This interface defines the contract for AI providers that can generate text content,
 * including captions, descriptions, and other text-based content.
 *
 * @version 1.0
 *
 * @since 2025-10-16
 */
interface TextGenerationInterface
{
    /**
     * Generate text content based on a prompt
     *
     * @param  string  $prompt  The input prompt
     * @param  array  $options  Additional options (model, temperature, max_tokens, etc.)
     * @return array Response containing generated text and metadata
     */
    public function generateText(string $prompt, array $options = []): array;

    /**
     * Generate text with streaming support
     *
     * @param  string  $prompt  The input prompt
     * @param  array  $options  Additional options
     * @return \Generator Generator yielding chunks of the response
     */
    public function generateTextStream(string $prompt, array $options = []): \Generator;

    /**
     * Generate a caption for social media content
     *
     * @param  string  $description  Description of the content
     * @param  string  $style  Caption style (motivational, casual, professional, etc.)
     * @param  array  $options  Additional options
     * @return array Response containing generated caption and metadata
     */
    public function generateCaption(string $description, string $style = 'casual', array $options = []): array;

    /**
     * Generate hashtags for content
     *
     * @param  string  $content  The content to generate hashtags for
     * @param  int  $count  Number of hashtags to generate
     * @param  array  $options  Additional options
     * @return array Response containing generated hashtags and metadata
     */
    public function generateHashtags(string $content, int $count = 10, array $options = []): array;

    /**
     * Generate content plan for social media
     *
     * @param  array  $brief  Content brief with industry, audience, voice, etc.
     * @param  int  $days  Number of days to plan for
     * @param  array  $options  Additional options
     * @return array Response containing content plan and metadata
     */
    public function generateContentPlan(array $brief, int $days = 7, array $options = []): array;
}
