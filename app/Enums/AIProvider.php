<?php

namespace App\Enums;

/**
 * Enum for AI providers
 *
 * This enum defines all supported AI providers in the system,
 * including their identifiers and display names.
 *
 * @version 1.0
 *
 * @since 2025-10-16
 */
enum AIProvider: string
{
    case OPENAI = 'openai';
    case ANTHROPIC = 'anthropic';
    case GOOGLE = 'google';
    case LOCAL = 'local';

    /**
     * Get the display name for the provider
     *
     * @return string Human-readable provider name
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::OPENAI => 'OpenAI',
            self::ANTHROPIC => 'Anthropic',
            self::GOOGLE => 'Google AI',
            self::LOCAL => 'Local AI (Ollama)',
        };
    }

    /**
     * Get the provider description
     *
     * @return string Provider description
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::OPENAI => 'OpenAI GPT models and DALL-E image generation',
            self::ANTHROPIC => 'Anthropic Claude models with advanced reasoning',
            self::GOOGLE => 'Google Gemini models and Imagen image generation',
            self::LOCAL => 'Local AI models via Ollama (free, privacy-focused)',
        };
    }

    /**
     * Check if the provider supports text generation
     *
     * @return bool True if text generation is supported
     */
    public function supportsTextGeneration(): bool
    {
        return true; // All providers support text generation
    }

    /**
     * Check if the provider supports image generation
     *
     * @return bool True if image generation is supported
     */
    public function supportsImageGeneration(): bool
    {
        return match ($this) {
            self::OPENAI => true,
            self::ANTHROPIC => false,
            self::GOOGLE => true,
            self::LOCAL => false,
        };
    }

    /**
     * Check if the provider supports content moderation
     *
     * @return bool True if moderation is supported
     */
    public function supportsModeration(): bool
    {
        return match ($this) {
            self::OPENAI => true,
            self::ANTHROPIC => false,
            self::GOOGLE => true,
            self::LOCAL => false,
        };
    }

    /**
     * Get the default fallback order
     *
     * @return array Array of providers in fallback order
     */
    public static function getFallbackOrder(): array
    {
        return [
            self::OPENAI,
            self::ANTHROPIC,
            self::GOOGLE,
            self::LOCAL,
        ];
    }

    /**
     * Get all provider values as array
     *
     * @return array Array of provider values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
