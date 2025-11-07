<?php

namespace App\Services\AI\Services;

use App\Enums\AIProvider;
use App\Models\AiGeneration;
use App\Models\AiUsage;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AI Cost Calculation Service
 *
 * This service handles cost calculation, tracking, and optimization
 * for all AI providers and usage patterns.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class CostCalculationService
{
    /**
     * Calculate cost for text generation
     */
    public function calculateTextCost(string $provider, string $model, int $tokensUsed): float
    {
        $costPerToken = $this->getCostPerToken($provider, $model);

        return $costPerToken * $tokensUsed;
    }

    /**
     * Calculate cost for image generation
     */
    public function calculateImageCost(string $provider, string $model, int $imageCount = 1): float
    {
        $costPerImage = $this->getCostPerImage($provider, $model);

        return $costPerImage * $imageCount;
    }

    /**
     * Get cost per token for a specific provider and model
     */
    public function getCostPerToken(string $provider, string $model): float
    {
        $config = config("ai.{$provider}.models.text.{$model}", []);

        return $config['cost_per_token'] ?? 0.0;
    }

    /**
     * Get cost per image for a specific provider and model
     */
    public function getCostPerImage(string $provider, string $model): float
    {
        $config = config("ai.{$provider}.models.image.{$model}", []);

        return $config['cost_per_image'] ?? 0.0;
    }

    /**
     * Track AI generation and cost
     */
    public function trackGeneration(
        int $companyId,
        int $userId,
        string $type,
        string $provider,
        string $model,
        string $prompt,
        ?string $result,
        int $tokensUsed = 0,
        float $cost = 0.0,
        array $metadata = []
    ): AiGeneration {
        try {
            $generation = AiGeneration::create([
                'company_id' => $companyId,
                'user_id' => $userId,
                'type' => $type,
                'provider' => $provider,
                'model' => $model,
                'prompt' => $prompt,
                'result' => $result,
                'tokens_used' => $tokensUsed,
                'cost_credits' => (int) ($cost * 1000000), // Convert to micro-dollars
                'metadata' => $metadata,
            ]);

            // Update daily usage statistics
            $this->updateDailyUsage($companyId, $userId, $provider, $model, $type, $tokensUsed, $cost);

            Log::info('AI generation tracked', [
                'generation_id' => $generation->id,
                'company_id' => $companyId,
                'user_id' => $userId,
                'provider' => $provider,
                'model' => $model,
                'type' => $type,
                'tokens_used' => $tokensUsed,
                'cost' => $cost,
            ]);

            return $generation;

        } catch (\Exception $e) {
            Log::error('Failed to track AI generation', [
                'error' => $e->getMessage(),
                'company_id' => $companyId,
                'user_id' => $userId,
                'provider' => $provider,
                'model' => $model,
            ]);

            throw $e;
        }
    }

    /**
     * Update daily usage statistics
     */
    private function updateDailyUsage(
        int $companyId,
        int $userId,
        string $provider,
        string $model,
        string $type,
        int $tokensUsed,
        float $cost
    ): void {
        $today = now()->toDateString();

        AiUsage::updateOrCreate(
            [
                'company_id' => $companyId,
                'user_id' => $userId,
                'provider' => $provider,
                'model' => $model,
                'type' => $type,
                'usage_date' => $today,
            ],
            [
                'tokens_used' => DB::raw("tokens_used + {$tokensUsed}"),
                'cost_usd' => DB::raw("cost_usd + {$cost}"),
                'requests_count' => DB::raw('requests_count + 1'),
            ]
        );
    }

    /**
     * Get company usage statistics
     */
    public function getCompanyUsage(int $companyId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = AiUsage::where('company_id', $companyId);

        if ($startDate && $endDate) {
            $query->whereBetween('usage_date', [$startDate, $endDate]);
        }

        $usage = $query->get();

        return [
            'total_tokens' => $usage->sum('tokens_used'),
            'total_cost' => $usage->sum('cost_usd'),
            'total_requests' => $usage->sum('requests_count'),
            'by_provider' => $usage->groupBy('provider')->map(function ($group) {
                return [
                    'tokens' => $group->sum('tokens_used'),
                    'cost' => $group->sum('cost_usd'),
                    'requests' => $group->sum('requests_count'),
                ];
            }),
            'by_type' => $usage->groupBy('type')->map(function ($group) {
                return [
                    'tokens' => $group->sum('tokens_used'),
                    'cost' => $group->sum('cost_usd'),
                    'requests' => $group->sum('requests_count'),
                ];
            }),
            'daily_breakdown' => $usage->groupBy('usage_date')->map(function ($group) {
                return [
                    'tokens' => $group->sum('tokens_used'),
                    'cost' => $group->sum('cost_usd'),
                    'requests' => $group->sum('requests_count'),
                ];
            }),
        ];
    }

    /**
     * Get user usage statistics
     */
    public function getUserUsage(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = AiUsage::where('user_id', $userId);

        if ($startDate && $endDate) {
            $query->whereBetween('usage_date', [$startDate, $endDate]);
        }

        $usage = $query->get();

        return [
            'total_tokens' => $usage->sum('tokens_used'),
            'total_cost' => $usage->sum('cost_usd'),
            'total_requests' => $usage->sum('requests_count'),
            'by_provider' => $usage->groupBy('provider')->map(function ($group) {
                return [
                    'tokens' => $group->sum('tokens_used'),
                    'cost' => $group->sum('cost_usd'),
                    'requests' => $group->sum('requests_count'),
                ];
            }),
            'by_type' => $usage->groupBy('type')->map(function ($group) {
                return [
                    'tokens' => $group->sum('tokens_used'),
                    'cost' => $group->sum('cost_usd'),
                    'requests' => $group->sum('requests_count'),
                ];
            }),
        ];
    }

    /**
     * Get cost comparison between providers
     */
    public function getCostComparison(string $type = 'text', int $tokens = 1000): array
    {
        $providers = AIProvider::values();
        $comparison = [];

        foreach ($providers as $provider) {
            $models = config("ai.{$provider}.models.{$type}", []);

            foreach ($models as $modelName => $modelConfig) {
                $costPerToken = $modelConfig['cost_per_token'] ?? 0.0;
                $totalCost = $costPerToken * $tokens;

                $comparison[] = [
                    'provider' => $provider,
                    'model' => $modelName,
                    'cost_per_token' => $costPerToken,
                    'total_cost' => $totalCost,
                    'tokens' => $tokens,
                ];
            }
        }

        // Sort by total cost (cheapest first)
        usort($comparison, fn ($a, $b) => $a['total_cost'] <=> $b['total_cost']);

        return $comparison;
    }

    /**
     * Get cheapest provider for a specific task
     */
    public function getCheapestProvider(string $type = 'text', int $tokens = 1000): ?array
    {
        $comparison = $this->getCostComparison($type, $tokens);

        return $comparison[0] ?? null;
    }

    /**
     * Check if company has exceeded budget limits
     */
    public function checkBudgetLimits(int $companyId): array
    {
        $dailyLimit = config('ai.cost_management.daily_limit_usd', 100);
        $monthlyLimit = config('ai.cost_management.monthly_limit_usd', 1000);

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();

        $dailyUsage = AiUsage::where('company_id', $companyId)
            ->where('usage_date', $today)
            ->sum('cost_usd');

        $monthlyUsage = AiUsage::where('company_id', $companyId)
            ->where('usage_date', '>=', $monthStart)
            ->sum('cost_usd');

        return [
            'daily_limit' => $dailyLimit,
            'daily_usage' => $dailyUsage,
            'daily_remaining' => max(0, $dailyLimit - $dailyUsage),
            'daily_exceeded' => $dailyUsage > $dailyLimit,
            'monthly_limit' => $monthlyLimit,
            'monthly_usage' => $monthlyUsage,
            'monthly_remaining' => max(0, $monthlyLimit - $monthlyUsage),
            'monthly_exceeded' => $monthlyUsage > $monthlyLimit,
        ];
    }

    /**
     * Get provider cost efficiency ranking
     */
    public function getProviderEfficiencyRanking(): array
    {
        $providers = AIProvider::values();
        $ranking = [];

        foreach ($providers as $provider) {
            $textModels = config("ai.{$provider}.models.text", []);
            $imageModels = config("ai.{$provider}.models.image", []);

            $avgTextCost = 0;
            $avgImageCost = 0;

            if (! empty($textModels)) {
                $textCosts = array_column($textModels, 'cost_per_token');
                $avgTextCost = array_sum($textCosts) / count($textCosts);
            }

            if (! empty($imageModels)) {
                $imageCosts = array_column($imageModels, 'cost_per_image');
                $avgImageCost = array_sum($imageCosts) / count($imageCosts);
            }

            $ranking[] = [
                'provider' => $provider,
                'avg_text_cost' => $avgTextCost,
                'avg_image_cost' => $avgImageCost,
                'text_models_count' => count($textModels),
                'image_models_count' => count($imageModels),
                'supports_text' => ! empty($textModels),
                'supports_image' => ! empty($imageModels),
            ];
        }

        // Sort by average text cost (cheapest first)
        usort($ranking, fn ($a, $b) => $a['avg_text_cost'] <=> $b['avg_text_cost']);

        return $ranking;
    }

    /**
     * Generate cost optimization recommendations
     */
    public function getOptimizationRecommendations(int $companyId): array
    {
        $usage = $this->getCompanyUsage($companyId, now()->subDays(30)->toDateString(), now()->toDateString());
        $efficiency = $this->getProviderEfficiencyRanking();
        $budget = $this->checkBudgetLimits($companyId);

        $recommendations = [];

        // Check if using expensive providers
        $expensiveProviders = array_filter($usage['by_provider']->toArray(), function ($usage) {
            return $usage['cost'] > 10; // More than $10 in last 30 days
        });

        if (! empty($expensiveProviders)) {
            $recommendations[] = [
                'type' => 'cost_reduction',
                'priority' => 'high',
                'title' => 'Consider switching to cheaper providers',
                'description' => 'You\'re spending significant amounts on expensive providers. Consider using cheaper alternatives for non-critical tasks.',
                'savings_potential' => '20-50%',
            ];
        }

        // Check budget limits
        if ($budget['daily_exceeded']) {
            $recommendations[] = [
                'type' => 'budget_management',
                'priority' => 'critical',
                'title' => 'Daily budget exceeded',
                'description' => 'You\'ve exceeded your daily AI spending limit. Consider implementing stricter usage controls.',
                'action_required' => true,
            ];
        }

        if ($budget['monthly_exceeded']) {
            $recommendations[] = [
                'type' => 'budget_management',
                'priority' => 'critical',
                'title' => 'Monthly budget exceeded',
                'description' => 'You\'ve exceeded your monthly AI spending limit. Review your usage patterns and consider upgrading your plan.',
                'action_required' => true,
            ];
        }

        // Check for free tier opportunities
        $freeProviders = array_filter($efficiency, fn ($provider) => $provider['avg_text_cost'] == 0);
        if (! empty($freeProviders) && $usage['total_cost'] > 0) {
            $recommendations[] = [
                'type' => 'free_tier',
                'priority' => 'medium',
                'title' => 'Use free providers when available',
                'description' => 'Consider using local AI providers for non-critical tasks to reduce costs.',
                'savings_potential' => '100% for applicable tasks',
            ];
        }

        return $recommendations;
    }
}
