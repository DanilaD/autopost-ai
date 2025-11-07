<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\AnalyticsRequest;
use App\Models\AiGeneration;
use App\Services\AI\Services\CostCalculationService;
use App\Services\AI\Services\SmartProviderSelectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * AI Analytics Controller
 *
 * Provides analytics, usage statistics, and cost tracking
 * for AI operations across all providers.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class AnalyticsController extends Controller
{
    private CostCalculationService $costService;

    private SmartProviderSelectionService $smartSelection;

    public function __construct(
        CostCalculationService $costService,
        SmartProviderSelectionService $smartSelection
    ) {
        $this->costService = $costService;
        $this->smartSelection = $smartSelection;
    }

    /**
     * Get company usage statistics
     */
    public function getCompanyUsage(AnalyticsRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());

            $usage = $this->costService->getCompanyUsage($companyId, $startDate, $endDate);

            // Filter by provider if specified
            if ($request->has('provider')) {
                $usage['by_provider'] = $usage['by_provider']->filter(function ($data, $provider) use ($request) {
                    return $provider === $request->input('provider');
                });
            }

            // Filter by type if specified
            if ($request->has('type')) {
                $usage['by_type'] = $usage['by_type']->filter(function ($data, $type) use ($request) {
                    return $type === $request->input('type');
                });
            }

            return response()->json([
                'success' => true,
                'usage' => $usage,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'filters' => [
                    'provider' => $request->input('provider'),
                    'type' => $request->input('type'),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get company usage', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'company_id' => $user->company_id ?? null,
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get user usage statistics
     */
    public function getUserUsage(AnalyticsRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $userId = $user->id;

            $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
            $endDate = $request->input('end_date', now()->toDateString());

            $usage = $this->costService->getUserUsage($userId, $startDate, $endDate);

            return response()->json([
                'success' => true,
                'usage' => $usage,
                'period' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
                'filters' => [
                    'provider' => $request->input('provider'),
                    'type' => $request->input('type'),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get user usage', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get budget status and limits
     */
    public function getBudgetStatus(): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            $budgetLimits = $this->costService->checkBudgetLimits($companyId);

            return response()->json([
                'success' => true,
                'budget_status' => $budgetLimits,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get budget status', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get cost comparison between providers
     */
    public function getCostComparison(AnalyticsRequest $request): JsonResponse
    {
        try {
            $type = $request->input('type', 'text');
            $tokens = $request->input('tokens', 1000);
            $imageCount = $request->input('image_count', 1);

            $comparison = $this->costService->getCostComparison($type, $tokens);

            return response()->json([
                'success' => true,
                'comparison' => $comparison,
                'parameters' => [
                    'type' => $type,
                    'tokens' => $tokens,
                    'image_count' => $imageCount,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get cost comparison', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get provider efficiency ranking
     */
    public function getProviderEfficiency(): JsonResponse
    {
        try {
            $efficiency = $this->costService->getProviderEfficiencyRanking();

            return response()->json([
                'success' => true,
                'efficiency_ranking' => $efficiency,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get provider efficiency', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get optimization recommendations
     */
    public function getOptimizationRecommendations(): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            $recommendations = $this->costService->getOptimizationRecommendations($companyId);

            return response()->json([
                'success' => true,
                'recommendations' => $recommendations,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get optimization recommendations', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get recent AI generations
     */
    public function getRecentGenerations(AnalyticsRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            $query = AiGeneration::where('company_id', $companyId)
                ->where('user_id', $user->id)
                ->orderBy('created_at', 'desc');

            if ($request->has('type')) {
                $query->where('type', $request->input('type'));
            }

            if ($request->has('provider')) {
                $query->where('provider', $request->input('provider'));
            }

            $generations = $query->limit($request->input('limit', 20))->get();

            return response()->json([
                'success' => true,
                'generations' => $generations->map(function ($generation) {
                    return [
                        'id' => $generation->id,
                        'type' => $generation->type,
                        'provider' => $generation->provider,
                        'model' => $generation->model,
                        'prompt' => substr($generation->prompt, 0, 100).'...',
                        'result' => substr($generation->result ?? '', 0, 200).'...',
                        'tokens_used' => $generation->tokens_used,
                        'cost_usd' => $generation->cost_usd,
                        'created_at' => $generation->created_at,
                    ];
                }),
                'filters' => [
                    'type' => $request->input('type'),
                    'provider' => $request->input('provider'),
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get recent generations', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get provider statistics
     */
    public function getProviderStats(): JsonResponse
    {
        try {
            $stats = $this->smartSelection->getProviderStats();

            return response()->json([
                'success' => true,
                'provider_stats' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get provider stats', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get fallback chain for a task type
     */
    public function getFallbackChain(AnalyticsRequest $request): JsonResponse
    {
        try {
            $type = $request->input('type', 'text');
            $fallbackChain = $this->smartSelection->getFallbackChain($type);

            return response()->json([
                'success' => true,
                'fallback_chain' => $fallbackChain,
                'type' => $type,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get fallback chain', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }
}
