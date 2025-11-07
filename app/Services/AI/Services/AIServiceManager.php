<?php

namespace App\Services\AI\Services;

use App\Enums\AIProvider;
use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Contracts\ImageGenerationInterface;
use App\Services\AI\Contracts\ModerationInterface;
use App\Services\AI\Contracts\TextGenerationInterface;
use App\Services\AI\Providers\AnthropicProvider;
use App\Services\AI\Providers\GoogleProvider;
use App\Services\AI\Providers\LocalProvider;
use App\Services\AI\Providers\OpenAIProvider;
use Illuminate\Support\Facades\Log;

/**
 * AI Service Manager
 *
 * This service manages all AI providers and implements the fallback chain
 * logic. It automatically selects the best available provider based on
 * availability, cost, and performance.
 *
 * @version 1.0
 *
 * @since 2025-10-16
 */
class AIServiceManager
{
    private array $providers = [];

    private array $fallbackChain = [];

    private bool $costOptimizationEnabled = true;

    private bool $freeTierPriority = true;

    /**
     * Initialize the AI Service Manager
     */
    public function __construct()
    {
        $this->fallbackChain = config('ai.fallback_chain', []);
        $this->costOptimizationEnabled = config('ai.cost_management.track_costs', true);
        $this->freeTierPriority = config('ai.cost_management.free_tier_priority', true);

        $this->initializeProviders();
    }

    /**
     * Initialize all available providers
     */
    private function initializeProviders(): void
    {
        // Initialize OpenAI provider
        $this->providers[AIProvider::OPENAI->value] = new OpenAIProvider;

        // Initialize Anthropic provider
        $this->providers[AIProvider::ANTHROPIC->value] = new AnthropicProvider;

        // Initialize Google AI provider
        $this->providers[AIProvider::GOOGLE->value] = new GoogleProvider;

        // Initialize Local AI provider (Ollama)
        $this->providers[AIProvider::LOCAL->value] = new LocalProvider;
    }

    /**
     * Get the best available provider for text generation
     */
    public function getTextProvider(?string $preferredProvider = null): ?TextGenerationInterface
    {
        $providers = $this->getProviderChain($preferredProvider);

        foreach ($providers as $providerName) {
            $provider = $this->providers[$providerName] ?? null;

            if ($provider && $provider instanceof TextGenerationInterface && $provider instanceof AIProviderInterface && $provider->isAvailable()) {
                // Check if we should prioritize free providers
                if ($this->freeTierPriority && $providerName === AIProvider::LOCAL->value) {
                    return $provider;
                }

                // For cost optimization, prefer cheaper providers
                if ($this->costOptimizationEnabled && $this->isProviderCheaper($provider, $providers)) {
                    return $provider;
                }

                return $provider;
            }
        }

        return null;
    }

    /**
     * Get the best available provider for image generation
     */
    public function getImageProvider(?string $preferredProvider = null): ?ImageGenerationInterface
    {
        $providers = $this->getProviderChain($preferredProvider);

        foreach ($providers as $providerName) {
            $provider = $this->providers[$providerName] ?? null;

            if ($provider && $provider instanceof ImageGenerationInterface && $provider instanceof AIProviderInterface && $provider->isAvailable()) {
                // Check if we should prioritize free providers
                if ($this->freeTierPriority && $providerName === AIProvider::LOCAL->value) {
                    return $provider;
                }

                // For cost optimization, prefer cheaper providers
                if ($this->costOptimizationEnabled && $this->isProviderCheaper($provider, $providers)) {
                    return $provider;
                }

                return $provider;
            }
        }

        return null;
    }

    /**
     * Get the best available provider for content moderation
     */
    public function getModerationProvider(?string $preferredProvider = null): ?ModerationInterface
    {
        $providers = $this->getProviderChain($preferredProvider);

        foreach ($providers as $providerName) {
            $provider = $this->providers[$providerName] ?? null;

            if ($provider && $provider instanceof ModerationInterface && $provider instanceof AIProviderInterface && $provider->isAvailable()) {
                return $provider;
            }
        }

        return null;
    }

    /**
     * Generate text using the best available provider
     */
    public function generateText(string $prompt, array $options = []): array
    {
        $provider = $this->getTextProvider($options['provider'] ?? null);

        if (! $provider) {
            return [
                'success' => false,
                'error' => 'No available text generation providers',
                'provider' => null,
            ];
        }

        try {
            $result = $provider->generateText($prompt, $options);

            // Log the generation for analytics
            $this->logGeneration($result, 'text', $prompt, $options);

            return $result;

        } catch (\Exception $e) {
            Log::error('AI text generation failed', [
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
                'prompt' => substr($prompt, 0, 100),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        }
    }

    /**
     * Generate text with streaming support
     */
    public function generateTextStream(string $prompt, array $options = []): \Generator
    {
        $provider = $this->getTextProvider($options['provider'] ?? null);

        if (! $provider) {
            yield [
                'error' => 'No available text generation providers',
                'provider' => null,
            ];

            return;
        }

        try {
            foreach ($provider->generateTextStream($prompt, $options) as $chunk) {
                yield $chunk;
            }

        } catch (\Exception $e) {
            Log::error('AI streaming text generation failed', [
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
                'prompt' => substr($prompt, 0, 100),
            ]);

            yield [
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        }
    }

    /**
     * Generate a caption using the best available provider
     */
    public function generateCaption(string $description, string $style = 'casual', array $options = []): array
    {
        $provider = $this->getTextProvider($options['provider'] ?? null);

        if (! $provider) {
            return [
                'success' => false,
                'error' => 'No available text generation providers',
                'provider' => null,
            ];
        }

        try {
            $result = $provider->generateCaption($description, $style, $options);

            // Log the generation for analytics
            $this->logGeneration($result, 'caption', $description, $options);

            return $result;

        } catch (\Exception $e) {
            Log::error('AI caption generation failed', [
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
                'description' => substr($description, 0, 100),
                'style' => $style,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        }
    }

    /**
     * Generate hashtags using the best available provider
     */
    public function generateHashtags(string $content, int $count = 10, array $options = []): array
    {
        $provider = $this->getTextProvider($options['provider'] ?? null);

        if (! $provider) {
            return [
                'success' => false,
                'error' => 'No available text generation providers',
                'provider' => null,
            ];
        }

        try {
            $result = $provider->generateHashtags($content, $count, $options);

            // Log the generation for analytics
            $this->logGeneration($result, 'hashtags', $content, $options);

            return $result;

        } catch (\Exception $e) {
            Log::error('AI hashtag generation failed', [
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
                'content' => substr($content, 0, 100),
                'count' => $count,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        }
    }

    /**
     * Generate content plan using the best available provider
     */
    public function generateContentPlan(array $brief, int $days = 7, array $options = []): array
    {
        $provider = $this->getTextProvider($options['provider'] ?? null);

        if (! $provider) {
            return [
                'success' => false,
                'error' => 'No available text generation providers',
                'provider' => null,
            ];
        }

        try {
            $result = $provider->generateContentPlan($brief, $days, $options);

            // Log the generation for analytics
            $this->logGeneration($result, 'plan', json_encode($brief), $options);

            return $result;

        } catch (\Exception $e) {
            Log::error('AI content plan generation failed', [
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
                'brief' => $brief,
                'days' => $days,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        }
    }

    /**
     * Generate an image using the best available provider
     */
    public function generateImage(string $prompt, array $options = []): array
    {
        $provider = $this->getImageProvider($options['provider'] ?? null);

        if (! $provider) {
            return [
                'success' => false,
                'error' => 'No available image generation providers',
                'provider' => null,
            ];
        }

        try {
            $result = $provider->generateImage($prompt, $options);

            // Log the generation for analytics
            $this->logGeneration($result, 'image', $prompt, $options);

            return $result;

        } catch (\Exception $e) {
            Log::error('AI image generation failed', [
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
                'prompt' => substr($prompt, 0, 100),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        }
    }

    /**
     * Generate multiple images
     */
    public function generateImages(string $prompt, int $count = 1, array $options = []): array
    {
        $provider = $this->getImageProvider($options['provider'] ?? null);

        if (! $provider) {
            return [
                'success' => false,
                'error' => 'No image generation providers available',
                'provider' => 'none',
            ];
        }

        try {
            $imageUrls = [];
            $totalCost = 0;
            $totalTokens = 0;

            for ($i = 0; $i < $count; $i++) {
                $result = $provider->generateImage($prompt, $options);
                if ($result['success']) {
                    $imageUrls[] = $result['image_url'];
                    $totalCost += $result['cost'] ?? 0;
                    $totalTokens += $result['tokens_used'] ?? 0;
                }
            }

            return [
                'success' => true,
                'image_urls' => $imageUrls,
                'count' => count($imageUrls),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
                'model' => $result['model'] ?? 'unknown',
                'cost' => $totalCost,
                'tokens_used' => $totalTokens,
            ];
        } catch (\Exception $e) {
            Log::error('Multiple image generation failed', [
                'error' => $e->getMessage(),
                'prompt' => $prompt,
                'count' => $count,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        }
    }

    /**
     * Edit an existing image
     */
    public function editImage(string $imagePath, string $prompt, array $options = []): array
    {
        $provider = $this->getImageProvider($options['provider'] ?? null);

        if (! $provider) {
            return [
                'success' => false,
                'error' => 'No image editing providers available',
                'provider' => 'none',
            ];
        }

        try {
            // For now, return not supported - this would need to be implemented in providers
            return [
                'success' => false,
                'error' => 'Image editing not yet implemented',
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        } catch (\Exception $e) {
            Log::error('Image editing failed', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath,
                'prompt' => $prompt,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        }
    }

    /**
     * Generate image variations
     */
    public function generateVariations(string $imagePath, int $count = 1, array $options = []): array
    {
        $provider = $this->getImageProvider($options['provider'] ?? null);

        if (! $provider) {
            return [
                'success' => false,
                'error' => 'No image variation providers available',
                'provider' => 'none',
            ];
        }

        try {
            // For now, return not supported - this would need to be implemented in providers
            return [
                'success' => false,
                'error' => 'Image variations not yet implemented',
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        } catch (\Exception $e) {
            Log::error('Image variation generation failed', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath,
                'count' => $count,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        }
    }

    /**
     * Moderate image content
     */
    public function moderateImage(string $imagePath, array $options = []): array
    {
        $provider = $this->getModerationProvider($options['provider'] ?? null);

        if (! $provider) {
            return [
                'success' => false,
                'error' => 'No image moderation providers available',
                'provider' => 'none',
            ];
        }

        try {
            // For now, return not supported - this would need to be implemented in providers
            return [
                'success' => false,
                'error' => 'Image moderation not yet implemented',
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        } catch (\Exception $e) {
            Log::error('Image moderation failed', [
                'error' => $e->getMessage(),
                'image_path' => $imagePath,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        }
    }

    /**
     * Moderate content using the best available provider
     */
    public function moderateContent(string $content, string $type = 'text', array $options = []): array
    {
        $provider = $this->getModerationProvider($options['provider'] ?? null);

        if (! $provider) {
            return [
                'success' => false,
                'error' => 'No available moderation providers',
                'provider' => null,
            ];
        }

        try {
            $result = $provider->moderateText($content, $options);

            // Log the moderation for analytics
            $this->logModeration($result, $content, $type, $options);

            return $result;

        } catch (\Exception $e) {
            Log::error('AI content moderation failed', [
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
                'content' => substr($content, 0, 100),
                'type' => $type,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
            ];
        }
    }

    /**
     * Check if content is safe for publication
     */
    public function isContentSafe(string $content, string $type = 'text', array $options = []): bool
    {
        $provider = $this->getModerationProvider($options['provider'] ?? null);

        if (! $provider) {
            return true; // Assume safe if no moderation available
        }

        try {
            return $provider->isContentSafe($content, $type, $options);

        } catch (\Exception $e) {
            Log::error('AI content safety check failed', [
                'error' => $e->getMessage(),
                'provider' => $provider instanceof AIProviderInterface ? $provider->getName() : 'unknown',
                'content' => substr($content, 0, 100),
                'type' => $type,
            ]);

            return true; // Assume safe on error
        }
    }

    /**
     * Get provider chain based on preference
     */
    private function getProviderChain(?string $preferredProvider = null): array
    {
        if ($preferredProvider && in_array($preferredProvider, $this->fallbackChain)) {
            // Move preferred provider to front
            $chain = array_filter($this->fallbackChain, fn ($p) => $p !== $preferredProvider);
            array_unshift($chain, $preferredProvider);

            return $chain;
        }

        return $this->fallbackChain;
    }

    /**
     * Check if a provider is cheaper than others in the chain
     */
    private function isProviderCheaper($provider, array $chain): bool
    {
        $providerName = $provider->getName();

        // Local AI is always cheapest (free)
        if ($providerName === AIProvider::LOCAL->value) {
            return true;
        }

        // Compare with other providers in the chain
        foreach ($chain as $otherProviderName) {
            if ($otherProviderName === $providerName) {
                continue;
            }

            $otherProvider = $this->providers[$otherProviderName] ?? null;
            if (! $otherProvider) {
                continue;
            }

            // Simple cost comparison - in real implementation, you'd compare
            // actual costs based on model and usage patterns
            $costRanking = [
                AIProvider::LOCAL->value => 0,      // Free
                AIProvider::GOOGLE->value => 1,     // Cheapest paid
                AIProvider::OPENAI->value => 2,     // Medium cost
                AIProvider::ANTHROPIC->value => 3,   // Most expensive
            ];

            $currentCost = $costRanking[$providerName] ?? 999;
            $otherCost = $costRanking[$otherProviderName] ?? 999;

            if ($currentCost > $otherCost) {
                return false;
            }
        }

        return true;
    }

    /**
     * Log generation for analytics and tracking
     */
    private function logGeneration(array $result, string $type, string $prompt, array $options): void
    {
        if (! $result['success']) {
            return;
        }

        Log::info('AI generation completed', [
            'type' => $type,
            'provider' => $result['provider'] ?? 'unknown',
            'model' => $result['model'] ?? 'unknown',
            'tokens_used' => $result['tokens_used'] ?? 0,
            'cost' => $result['cost'] ?? 0,
            'prompt_length' => strlen($prompt),
            'options' => $options,
        ]);
    }

    /**
     * Log moderation for analytics and tracking
     */
    private function logModeration(array $result, string $content, string $type, array $options): void
    {
        if (! $result['success']) {
            return;
        }

        Log::info('AI moderation completed', [
            'type' => $type,
            'provider' => $result['provider'] ?? 'unknown',
            'flagged' => $result['flagged'] ?? false,
            'categories' => $result['categories'] ?? [],
            'content_length' => strlen($content),
            'options' => $options,
        ]);
    }

    /**
     * Get all available providers
     */
    public function getAvailableProviders(): array
    {
        $available = [];

        foreach ($this->providers as $name => $provider) {
            if ($provider->isAvailable()) {
                $available[$name] = [
                    'name' => $provider->getName(),
                    'available' => true,
                    'models' => $provider->getAvailableModels(),
                ];
            }
        }

        return $available;
    }

    /**
     * Get provider statistics
     */
    public function getProviderStats(): array
    {
        $stats = [];

        foreach ($this->providers as $name => $provider) {
            $stats[$name] = [
                'name' => $provider->getName(),
                'available' => $provider->isAvailable(),
                'models' => $provider->getAvailableModels(),
                'supports_text' => $provider instanceof TextGenerationInterface,
                'supports_image' => $provider instanceof ImageGenerationInterface,
                'supports_moderation' => $provider instanceof ModerationInterface,
            ];
        }

        return $stats;
    }
}
