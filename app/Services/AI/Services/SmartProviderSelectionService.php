<?php

namespace App\Services\AI\Services;

use App\Enums\AIProvider;
use App\Models\AiModel;
use App\Models\AiUsage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Smart Provider Selection Service
 *
 * This service intelligently selects the best AI provider based on:
 * - Cost optimization
 * - Provider availability
 * - Performance requirements
 * - Usage patterns and limits
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class SmartProviderSelectionService
{
    private CostCalculationService $costService;

    private array $providerWeights = [];

    private array $performanceMetrics = [];

    /**
     * Initialize the smart selection service
     */
    public function __construct(CostCalculationService $costService)
    {
        $this->costService = $costService;
        $this->loadProviderWeights();
        $this->loadPerformanceMetrics();
    }

    /**
     * Select the best provider for text generation
     */
    public function selectTextProvider(
        ?string $preferredProvider = null,
        ?int $companyId = null,
        bool $prioritizeFree = false,
        bool $prioritizeSpeed = false,
        int $estimatedTokens = 1000
    ): ?array {
        $providers = $this->getAvailableTextProviders();

        if (empty($providers)) {
            return null;
        }

        // If preferred provider is specified and available, use it
        if ($preferredProvider && isset($providers[$preferredProvider])) {
            return $providers[$preferredProvider];
        }

        // Score each provider based on criteria
        $scoredProviders = [];

        foreach ($providers as $providerName => $provider) {
            $score = $this->calculateProviderScore(
                $providerName,
                $provider,
                $companyId,
                $prioritizeFree,
                $prioritizeSpeed,
                $estimatedTokens,
                'text'
            );

            $scoredProviders[$providerName] = [
                'provider' => $provider,
                'score' => $score,
                'cost' => $this->costService->calculateTextCost($providerName, $provider['model'], $estimatedTokens),
                'is_free' => $this->costService->getCostPerToken($providerName, $provider['model']) == 0,
            ];
        }

        // Sort by score (highest first)
        uasort($scoredProviders, fn ($a, $b) => $b['score'] <=> $a['score']);

        $bestProvider = array_key_first($scoredProviders);

        Log::info('Smart provider selection', [
            'selected_provider' => $bestProvider,
            'scores' => $scoredProviders,
            'criteria' => [
                'prioritize_free' => $prioritizeFree,
                'prioritize_speed' => $prioritizeSpeed,
                'estimated_tokens' => $estimatedTokens,
            ],
        ]);

        return $scoredProviders[$bestProvider] ?? null;
    }

    /**
     * Select the best provider for image generation
     */
    public function selectImageProvider(
        ?string $preferredProvider = null,
        ?int $companyId = null,
        bool $prioritizeFree = false,
        int $imageCount = 1
    ): ?array {
        $providers = $this->getAvailableImageProviders();

        if (empty($providers)) {
            return null;
        }

        // If preferred provider is specified and available, use it
        if ($preferredProvider && isset($providers[$preferredProvider])) {
            return $providers[$preferredProvider];
        }

        // Score each provider based on criteria
        $scoredProviders = [];

        foreach ($providers as $providerName => $provider) {
            $score = $this->calculateProviderScore(
                $providerName,
                $provider,
                $companyId,
                $prioritizeFree,
                false, // Speed not as important for images
                $imageCount,
                'image'
            );

            $scoredProviders[$providerName] = [
                'provider' => $provider,
                'score' => $score,
                'cost' => $this->costService->calculateImageCost($providerName, $provider['model'], $imageCount),
                'is_free' => $this->costService->getCostPerImage($providerName, $provider['model']) == 0,
            ];
        }

        // Sort by score (highest first)
        uasort($scoredProviders, fn ($a, $b) => $b['score'] <=> $a['score']);

        $bestProvider = array_key_first($scoredProviders);

        return $scoredProviders[$bestProvider] ?? null;
    }

    /**
     * Get available text generation providers
     */
    private function getAvailableTextProviders(): array
    {
        return Cache::remember('available_text_providers', 300, function () {
            $providers = [];

            $models = AiModel::where('is_active', true)
                ->whereIn('type', ['text', 'multimodal'])
                ->get();

            foreach ($models as $model) {
                $providers[$model->provider] = [
                    'model' => $model->name,
                    'cost_per_token' => $model->cost_per_token,
                    'capabilities' => $model->capabilities,
                    'description' => $model->description,
                ];
            }

            return $providers;
        });
    }

    /**
     * Get available image generation providers
     */
    private function getAvailableImageProviders(): array
    {
        return Cache::remember('available_image_providers', 300, function () {
            $providers = [];

            $models = AiModel::where('is_active', true)
                ->where('type', 'image')
                ->get();

            foreach ($models as $model) {
                $providers[$model->provider] = [
                    'model' => $model->name,
                    'cost_per_image' => $model->cost_per_image,
                    'capabilities' => $model->capabilities,
                    'description' => $model->description,
                ];
            }

            return $providers;
        });
    }

    /**
     * Calculate provider score based on multiple criteria
     */
    private function calculateProviderScore(
        string $providerName,
        array $provider,
        ?int $companyId,
        bool $prioritizeFree,
        bool $prioritizeSpeed,
        int $estimatedTokens,
        string $type
    ): float {
        $score = 0.0;

        // Base score from provider weights
        $score += $this->providerWeights[$providerName] ?? 0.5;

        // Cost factor (lower cost = higher score)
        $costPerUnit = $type === 'text'
            ? ($provider['cost_per_token'] ?? 0)
            : ($provider['cost_per_image'] ?? 0);

        if ($costPerUnit == 0) {
            $score += 2.0; // Free providers get bonus
        } else {
            $score += max(0, 1.0 - ($costPerUnit * 1000)); // Lower cost = higher score
        }

        // Free provider bonus
        if ($prioritizeFree && $costPerUnit == 0) {
            $score += 1.5;
        }

        // Performance factor
        $performanceScore = $this->performanceMetrics[$providerName] ?? 0.5;
        if ($prioritizeSpeed) {
            $score += $performanceScore * 1.5;
        } else {
            $score += $performanceScore;
        }

        // Company budget factor
        if ($companyId) {
            $budgetLimits = $this->costService->checkBudgetLimits($companyId);

            if ($budgetLimits['daily_exceeded'] || $budgetLimits['monthly_exceeded']) {
                // Prioritize free providers when budget exceeded
                if ($costPerUnit == 0) {
                    $score += 3.0;
                } else {
                    $score -= 2.0;
                }
            }
        }

        // Usage pattern factor
        $usageScore = $this->getUsagePatternScore($providerName, $companyId);
        $score += $usageScore;

        // Reliability factor (based on historical success rate)
        $reliabilityScore = $this->getReliabilityScore($providerName);
        $score += $reliabilityScore;

        return max(0, $score);
    }

    /**
     * Load provider weights from configuration
     */
    private function loadProviderWeights(): void
    {
        $this->providerWeights = [
            AIProvider::LOCAL->value => 1.0,      // Local AI gets highest weight (free, private)
            AIProvider::GOOGLE->value => 0.8,      // Google AI gets high weight (good performance/cost)
            AIProvider::ANTHROPIC->value => 0.7,   // Anthropic gets medium-high weight (good quality)
            AIProvider::OPENAI->value => 0.6,      // OpenAI gets medium weight (reliable but expensive)
        ];
    }

    /**
     * Load performance metrics from configuration
     */
    private function loadPerformanceMetrics(): void
    {
        $this->performanceMetrics = [
            AIProvider::LOCAL->value => 0.6,       // Local AI is slower but free
            AIProvider::GOOGLE->value => 0.9,      // Google AI is fast
            AIProvider::ANTHROPIC->value => 0.8,   // Anthropic is fast
            AIProvider::OPENAI->value => 0.9,      // OpenAI is very fast
        ];
    }

    /**
     * Get usage pattern score for a provider
     */
    private function getUsagePatternScore(string $providerName, ?int $companyId): float
    {
        if (! $companyId) {
            return 0.0;
        }

        try {
            $recentUsage = AiUsage::where('company_id', $companyId)
                ->where('provider', $providerName)
                ->where('usage_date', '>=', now()->subDays(7))
                ->sum('requests_count');

            // Prefer providers with less recent usage (load balancing)
            if ($recentUsage == 0) {
                return 0.5; // Bonus for unused providers
            } elseif ($recentUsage < 10) {
                return 0.2; // Small bonus for lightly used providers
            } else {
                return -0.1; // Small penalty for heavily used providers
            }
        } catch (\Exception $e) {
            Log::warning('Failed to calculate usage pattern score', [
                'provider' => $providerName,
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);

            return 0.0;
        }
    }

    /**
     * Get reliability score for a provider
     */
    private function getReliabilityScore(string $providerName): float
    {
        // This could be enhanced with actual reliability metrics
        // For now, return base reliability scores
        return match ($providerName) {
            AIProvider::OPENAI->value => 0.9,      // OpenAI is very reliable
            AIProvider::GOOGLE->value => 0.8,      // Google AI is reliable
            AIProvider::ANTHROPIC->value => 0.8,   // Anthropic is reliable
            AIProvider::LOCAL->value => 0.7,       // Local AI depends on local setup
            default => 0.5,
        };
    }

    /**
     * Get fallback chain for a specific task type
     */
    public function getFallbackChain(string $type = 'text'): array
    {
        $providers = $type === 'text'
            ? $this->getAvailableTextProviders()
            : $this->getAvailableImageProviders();

        $fallbackChain = [];

        foreach ($providers as $providerName => $provider) {
            $score = $this->calculateProviderScore(
                $providerName,
                $provider,
                null,
                true, // Prioritize free
                false,
                1000,
                $type
            );

            $fallbackChain[] = [
                'provider' => $providerName,
                'model' => $provider['model'],
                'score' => $score,
                'is_free' => $type === 'text'
                    ? ($provider['cost_per_token'] ?? 0) == 0
                    : ($provider['cost_per_image'] ?? 0) == 0,
            ];
        }

        // Sort by score (highest first)
        usort($fallbackChain, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $fallbackChain;
    }

    /**
     * Get provider recommendations for a company
     */
    public function getProviderRecommendations(int $companyId): array
    {
        $budgetLimits = $this->costService->checkBudgetLimits($companyId);
        $usage = $this->costService->getCompanyUsage($companyId, now()->subDays(30)->toDateString(), now()->toDateString());

        $recommendations = [];

        // Budget-based recommendations
        if ($budgetLimits['daily_exceeded'] || $budgetLimits['monthly_exceeded']) {
            $recommendations[] = [
                'type' => 'budget',
                'priority' => 'high',
                'title' => 'Switch to free providers',
                'description' => 'Your budget limits are exceeded. Consider using local AI providers for non-critical tasks.',
                'providers' => ['local'],
            ];
        }

        // Cost optimization recommendations
        $expensiveProviders = array_filter($usage['by_provider']->toArray(), function ($usage) {
            return $usage['cost'] > 5; // More than $5 in last 30 days
        });

        if (! empty($expensiveProviders)) {
            $recommendations[] = [
                'type' => 'cost',
                'priority' => 'medium',
                'title' => 'Optimize provider usage',
                'description' => 'Consider using cheaper providers for routine tasks.',
                'providers' => ['local', 'google'],
            ];
        }

        // Performance recommendations
        if ($usage['total_requests'] > 100) {
            $recommendations[] = [
                'type' => 'performance',
                'priority' => 'low',
                'title' => 'Consider premium providers for critical tasks',
                'description' => 'For high-volume usage, consider using premium providers for better performance.',
                'providers' => ['openai', 'anthropic'],
            ];
        }

        return $recommendations;
    }

    /**
     * Update provider weights based on performance data
     */
    public function updateProviderWeights(): void
    {
        // This could be enhanced to dynamically adjust weights based on:
        // - Success rates
        // - Response times
        // - Error rates
        Log::info('Provider weights updated', [
            'weights' => $this->providerWeights,
        ]);
    }

    /**
     * Get provider statistics
     */
    public function getProviderStats(): array
    {
        $stats = [];

        $providers = [
            'openai' => ['supports_text' => true, 'supports_image' => true, 'supports_moderation' => true],
            'anthropic' => ['supports_text' => true, 'supports_image' => false, 'supports_moderation' => false],
            'google' => ['supports_text' => true, 'supports_image' => true, 'supports_moderation' => true],
            'local' => ['supports_text' => true, 'supports_image' => false, 'supports_moderation' => false],
        ];

        foreach ($providers as $provider => $capabilities) {
            $stats[$provider] = [
                'available' => $this->isProviderAvailable($provider),
                'supports_text' => $capabilities['supports_text'],
                'supports_image' => $capabilities['supports_image'],
                'supports_moderation' => $capabilities['supports_moderation'],
            ];
        }

        return $stats;
    }

    /**
     * Check if a provider is available
     */
    private function isProviderAvailable(string $provider): bool
    {
        switch ($provider) {
            case 'openai':
                return ! empty(config('ai.openai.key'));
            case 'anthropic':
                return ! empty(config('ai.anthropic.key'));
            case 'google':
                return ! empty(config('ai.google.key'));
            case 'local':
                // Check if Ollama is running
                try {
                    $response = \Http::timeout(5)->get('http://localhost:11434/api/tags');

                    return $response->successful();
                } catch (\Exception $e) {
                    return false;
                }
            default:
                return false;
        }
    }
}
