<?php

namespace App\Services\AI\Contracts;

/**
 * Base interface for all AI providers
 *
 * This interface defines the common contract that all AI providers must implement,
 * ensuring consistency across different AI services like OpenAI, Anthropic, Google AI, etc.
 *
 * @version 1.0
 *
 * @since 2025-10-16
 */
interface AIProviderInterface
{
    /**
     * Get the provider name
     *
     * @return string The provider identifier (e.g., 'openai', 'anthropic', 'google', 'local')
     */
    public function getName(): string;

    /**
     * Check if the provider is available and properly configured
     *
     * @return bool True if the provider is available, false otherwise
     */
    public function isAvailable(): bool;

    /**
     * Get the cost per token for text generation
     *
     * @param  string  $model  The model name
     * @return float Cost per token in USD
     */
    public function getCostPerToken(string $model): float;

    /**
     * Get the cost per image for image generation
     *
     * @param  string  $model  The model name
     * @return float Cost per image in USD
     */
    public function getCostPerImage(string $model): float;

    /**
     * Get available models for this provider
     *
     * @return array Array of available model names
     */
    public function getAvailableModels(): array;

    /**
     * Test the provider connection
     *
     * @return bool True if connection is successful, false otherwise
     */
    public function testConnection(): bool;
}
