<?php

namespace Tests\Feature\AI;

use App\Services\AI\Services\AIServiceManager;
use App\Services\AI\Services\CostCalculationService;
use App\Services\AI\Services\SmartProviderSelectionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * AI Service Manager Test
 *
 * Tests the AI Service Manager functionality including provider selection,
 * fallback logic, and cost optimization.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class AIServiceManagerTest extends TestCase
{
    use RefreshDatabase;

    private AIServiceManager $aiManager;

    private CostCalculationService $costService;

    private SmartProviderSelectionService $smartSelection;

    protected function setUp(): void
    {
        parent::setUp();

        // RefreshDatabase trait handles migrations automatically
        // Seed AI models for testing
        $this->seed(\Database\Seeders\AiModelSeeder::class);

        $this->aiManager = new AIServiceManager;
        $this->costService = new CostCalculationService;
        $this->smartSelection = new SmartProviderSelectionService($this->costService);
    }

    /**
     * Test AI Service Manager initialization
     */
    public function test_ai_service_manager_initializes_correctly(): void
    {
        $this->assertInstanceOf(AIServiceManager::class, $this->aiManager);

        $providers = $this->aiManager->getAvailableProviders();
        $this->assertIsArray($providers);

        // In test environment, at least one provider should be available
        $this->assertNotEmpty($providers, 'At least one AI provider should be available');

        // Check that the available providers have the expected structure
        foreach ($providers as $providerName => $provider) {
            $this->assertArrayHasKey('name', $provider);
            $this->assertArrayHasKey('available', $provider);
            $this->assertArrayHasKey('models', $provider);
            $this->assertTrue($provider['available']);
        }
    }

    /**
     * Test provider availability checking
     */
    public function test_provider_availability_checking(): void
    {
        $stats = $this->aiManager->getProviderStats();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('openai', $stats);
        $this->assertArrayHasKey('anthropic', $stats);
        $this->assertArrayHasKey('google', $stats);
        $this->assertArrayHasKey('local', $stats);

        // Check that each provider has the expected structure
        foreach ($stats as $provider => $stat) {
            $this->assertArrayHasKey('available', $stat);
            $this->assertArrayHasKey('supports_text', $stat);
            $this->assertArrayHasKey('supports_image', $stat);
            $this->assertArrayHasKey('supports_moderation', $stat);
        }
    }

    /**
     * Test text generation with fallback
     */
    public function test_text_generation_with_fallback(): void
    {
        // This test will use the first available provider
        $result = $this->aiManager->generateText('Test prompt for AI generation');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);

        // If successful, check the response structure
        if ($result['success']) {
            $this->assertArrayHasKey('content', $result);
            $this->assertArrayHasKey('provider', $result);
            $this->assertArrayHasKey('model', $result);
            $this->assertArrayHasKey('tokens_used', $result);
            $this->assertArrayHasKey('cost', $result);
        }
    }

    /**
     * Test caption generation
     */
    public function test_caption_generation(): void
    {
        $result = $this->aiManager->generateCaption(
            'A beautiful sunset over the ocean',
            'motivational'
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);

        if ($result['success']) {
            $this->assertArrayHasKey('content', $result);
            $this->assertArrayHasKey('style', $result);
            $this->assertEquals('motivational', $result['style']);
        }
    }

    /**
     * Test hashtag generation
     */
    public function test_hashtag_generation(): void
    {
        $result = $this->aiManager->generateHashtags(
            'A beautiful sunset over the ocean with peaceful waves',
            5
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);

        if ($result['success']) {
            $this->assertArrayHasKey('hashtags', $result);
            $this->assertIsArray($result['hashtags']);
            $this->assertLessThanOrEqual(5, count($result['hashtags']));
        }
    }

    /**
     * Test content plan generation
     */
    public function test_content_plan_generation(): void
    {
        $brief = [
            'industry' => 'technology',
            'target_audience' => 'developers',
            'brand_voice' => 'professional',
            'goals' => 'educate and engage',
        ];

        $result = $this->aiManager->generateContentPlan($brief, 7);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);

        if ($result['success']) {
            $this->assertArrayHasKey('content', $result);
            $this->assertArrayHasKey('days', $result);
            $this->assertEquals(7, $result['days']);
        }
    }

    /**
     * Test content moderation
     */
    public function test_content_moderation(): void
    {
        $result = $this->aiManager->moderateContent('This is a wonderful day with beautiful weather!');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);

        if ($result['success']) {
            $this->assertArrayHasKey('flagged', $result);
            $this->assertArrayHasKey('categories', $result);
            $this->assertArrayHasKey('category_scores', $result);
            $this->assertIsBool($result['flagged']);
        }
    }

    /**
     * Test smart provider selection
     */
    public function test_smart_provider_selection(): void
    {
        $provider = $this->smartSelection->selectTextProvider(
            companyId: 1,
            prioritizeFree: true,
            estimatedTokens: 1000
        );

        $this->assertIsArray($provider);
        $this->assertArrayHasKey('provider', $provider);
        $this->assertArrayHasKey('score', $provider);
        $this->assertArrayHasKey('cost', $provider);
        $this->assertArrayHasKey('is_free', $provider);
    }

    /**
     * Test cost calculation
     */
    public function test_cost_calculation(): void
    {
        $cost = $this->costService->calculateTextCost('openai', 'gpt-4o-mini', 1000);

        $this->assertIsFloat($cost);
        $this->assertGreaterThanOrEqual(0, $cost);
    }

    /**
     * Test fallback chain
     */
    public function test_fallback_chain(): void
    {
        $fallbackChain = $this->smartSelection->getFallbackChain('text');

        $this->assertIsArray($fallbackChain);
        $this->assertNotEmpty($fallbackChain);

        // Check that each item in the fallback chain has the expected structure
        foreach ($fallbackChain as $item) {
            $this->assertArrayHasKey('provider', $item);
            $this->assertArrayHasKey('model', $item);
            $this->assertArrayHasKey('score', $item);
            $this->assertArrayHasKey('is_free', $item);
        }
    }

    /**
     * Test provider efficiency ranking
     */
    public function test_provider_efficiency_ranking(): void
    {
        $efficiency = $this->costService->getProviderEfficiencyRanking();

        $this->assertIsArray($efficiency);
        $this->assertNotEmpty($efficiency);

        // Check that each provider has efficiency data
        foreach ($efficiency as $provider) {
            $this->assertArrayHasKey('provider', $provider);
            $this->assertArrayHasKey('avg_text_cost', $provider);
            $this->assertArrayHasKey('avg_image_cost', $provider);
            $this->assertArrayHasKey('supports_text', $provider);
            $this->assertArrayHasKey('supports_image', $provider);
        }
    }

    /**
     * Test cost comparison
     */
    public function test_cost_comparison(): void
    {
        $comparison = $this->costService->getCostComparison('text', 1000);

        $this->assertIsArray($comparison);
        $this->assertNotEmpty($comparison);

        // Check that comparison is sorted by cost (cheapest first)
        $previousCost = 0;
        foreach ($comparison as $item) {
            $this->assertArrayHasKey('provider', $item);
            $this->assertArrayHasKey('model', $item);
            $this->assertArrayHasKey('total_cost', $item);
            $this->assertGreaterThanOrEqual($previousCost, $item['total_cost']);
            $previousCost = $item['total_cost'];
        }
    }
}
