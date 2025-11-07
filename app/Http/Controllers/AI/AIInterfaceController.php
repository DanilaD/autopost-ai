<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\Services\AIServiceManager;
use App\Services\AI\Services\CostCalculationService;
use App\Services\AI\Services\SmartProviderSelectionService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * AI Interface Controller
 *
 * Provides the main AI interface for users to interact with AI features.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class AIInterfaceController extends Controller
{
    private AIServiceManager $aiManager;

    private CostCalculationService $costService;

    private SmartProviderSelectionService $smartSelection;

    public function __construct(
        AIServiceManager $aiManager,
        CostCalculationService $costService,
        SmartProviderSelectionService $smartSelection
    ) {
        $this->aiManager = $aiManager;
        $this->costService = $costService;
        $this->smartSelection = $smartSelection;
    }

    /**
     * Show the main AI interface
     */
    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // Get available providers
        $providers = $this->aiManager->getAvailableProviders();
        $providerStats = $this->aiManager->getProviderStats();

        // Get company usage statistics (only if user has a company)
        $usageStats = $companyId ? $this->costService->getCompanyUsage($companyId) : [
            'total_generations' => 0,
            'total_cost' => 0,
            'total_tokens' => 0,
            'by_provider' => [],
            'by_type' => [],
        ];

        // Get recent generations (only if user has a company)
        $recentGenerations = [];

        // Get cost comparison
        $costComparison = $this->costService->getCostComparison('text', 1000);

        // Get fallback chain
        $fallbackChain = $this->smartSelection->getFallbackChain('text');

        return Inertia::render('AI/Index', [
            'providers' => $providers,
            'providerStats' => $providerStats,
            'usageStats' => $usageStats,
            'recentGenerations' => $recentGenerations,
            'costComparison' => $costComparison,
            'fallbackChain' => $fallbackChain,
        ]);
    }

    /**
     * Show AI text generation interface
     */
    public function textGeneration()
    {
        $user = Auth::user();

        // Get available text providers
        $providers = $this->aiManager->getAvailableProviders();
        $textProviders = array_filter($providers, function ($provider) {
            return ! empty($provider['models']['text'] ?? []);
        });

        return Inertia::render('AI/TextGeneration', [
            'providers' => $textProviders,
        ]);
    }

    /**
     * Show AI image generation interface
     */
    public function imageGeneration()
    {
        $user = Auth::user();

        // Get available image providers
        $providers = $this->aiManager->getAvailableProviders();
        $imageProviders = array_filter($providers, function ($provider) {
            return ! empty($provider['models']['image'] ?? []);
        });

        return Inertia::render('AI/ImageGeneration', [
            'providers' => $imageProviders,
        ]);
    }

    /**
     * Show AI analytics interface
     */
    public function analytics()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        // Get comprehensive analytics (only if user has a company)
        $usageStats = $companyId ? $this->costService->getCompanyUsage($companyId) : [
            'total_generations' => 0,
            'total_cost' => 0,
            'total_tokens' => 0,
            'by_provider' => [],
            'by_type' => [],
        ];

        $userStats = $this->costService->getUserUsage($user->id);
        $costComparison = $this->costService->getCostComparison();
        $providerEfficiency = $this->costService->getProviderEfficiencyRanking();
        $recommendations = $companyId ? $this->costService->getOptimizationRecommendations($companyId) : [];

        return Inertia::render('AI/Analytics', [
            'usageStats' => $usageStats,
            'userStats' => $userStats,
            'costComparison' => $costComparison,
            'providerEfficiency' => $providerEfficiency,
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Show AI chat interface
     */
    public function chat()
    {
        $user = Auth::user();

        // Get available chat providers (all text providers can be used for chat)
        $providers = $this->aiManager->getAvailableProviders();
        $chatProviders = array_filter($providers, function ($provider) {
            return ! empty($provider['models']['text'] ?? []);
        });

        return Inertia::render('AI/Chat', [
            'providers' => $chatProviders,
        ]);
    }
}
