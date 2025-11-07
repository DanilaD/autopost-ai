<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\TextGenerationRequest;
use App\Services\AI\Services\AIServiceManager;
use App\Services\AI\Services\CostCalculationService;
use App\Services\AI\Services\SmartProviderSelectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * AI Text Controller
 *
 * Handles text generation tasks including captions, hashtags,
 * content plans, and general text generation.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class TextController extends Controller
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
     * Helper method to return appropriate response based on request type
     */
    private function returnResponse($data, $status = 200)
    {
        if (request()->header('X-Inertia')) {
            return back()->with('data', $data);
        }

        return response()->json($data, $status);
    }

    /**
     * Generate a social media caption
     */
    public function generateCaption(TextGenerationRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->current_company_id;

            // Select provider
            $provider = $this->smartSelection->selectTextProvider(
                preferredProvider: $request->input('provider'),
                companyId: $companyId,
                estimatedTokens: $request->input('max_tokens', 200)
            );

            if (! $provider) {
                return response()->json([
                    'success' => false,
                    'error' => 'No AI providers available',
                ], 503);
            }

            $options = [
                'model' => $request->input('model') ?: $provider['model'],
                'temperature' => $request->input('temperature', 0.7),
                'max_tokens' => $request->input('max_tokens', 200),
            ];

            $result = $this->aiManager->generateCaption(
                $request->input('description'),
                $request->input('style', 'casual'),
                $options
            );

            if ($result['success']) {
                // Track the generation
                $generation = $this->costService->trackGeneration(
                    companyId: $companyId,
                    userId: $user->id,
                    type: 'caption',
                    provider: $result['provider'],
                    model: $result['model'],
                    prompt: $request->input('description'),
                    result: $result['content'],
                    tokensUsed: $result['tokens_used'],
                    cost: $result['cost'],
                    metadata: [
                        'style' => $result['style'],
                        'temperature' => $options['temperature'],
                        'max_tokens' => $options['max_tokens'],
                    ]
                );

                return response()->json([
                    'success' => true,
                    'content' => $result['content'],
                    'style' => $result['style'],
                    'provider' => $result['provider'],
                    'model' => $result['model'],
                    'tokens_used' => $result['tokens_used'],
                    'cost' => $result['cost'],
                    'generation_id' => $generation->id,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Caption generation failed',
                'provider' => $result['provider'] ?? 'unknown',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Caption generation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'description' => $request->input('description'),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Generate hashtags for content
     */
    public function generateHashtags(TextGenerationRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->current_company_id;

            // Select provider
            $provider = $this->smartSelection->selectTextProvider(
                preferredProvider: $request->input('provider'),
                companyId: $companyId,
                estimatedTokens: 100
            );

            if (! $provider) {
                return response()->json([
                    'success' => false,
                    'error' => 'No AI providers available',
                ], 503);
            }

            $options = [
                'model' => $request->input('model') ?: $provider['model'],
                'temperature' => $request->input('temperature', 0.7),
            ];

            $result = $this->aiManager->generateHashtags(
                $request->input('content'),
                $request->input('count', 10),
                $options
            );

            if ($result['success']) {
                // Track the generation
                $generation = $this->costService->trackGeneration(
                    companyId: $companyId,
                    userId: $user->id,
                    type: 'hashtags',
                    provider: $result['provider'],
                    model: $result['model'],
                    prompt: $request->input('content'),
                    result: implode(', ', $result['hashtags']),
                    tokensUsed: $result['tokens_used'],
                    cost: $result['cost'],
                    metadata: [
                        'hashtag_count' => count($result['hashtags']),
                        'hashtags' => $result['hashtags'],
                        'temperature' => $options['temperature'],
                    ]
                );

                return response()->json([
                    'success' => true,
                    'hashtags' => $result['hashtags'],
                    'count' => count($result['hashtags']),
                    'provider' => $result['provider'],
                    'model' => $result['model'],
                    'tokens_used' => $result['tokens_used'],
                    'cost' => $result['cost'],
                    'generation_id' => $generation->id,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Hashtag generation failed',
                'provider' => $result['provider'] ?? 'unknown',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Hashtag generation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'content' => $request->input('content'),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Generate content plan
     */
    public function generateContentPlan(TextGenerationRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->current_company_id;

            // Select provider
            $provider = $this->smartSelection->selectTextProvider(
                preferredProvider: $request->input('provider'),
                companyId: $companyId,
                estimatedTokens: $request->input('max_tokens', 1000)
            );

            if (! $provider) {
                return response()->json([
                    'success' => false,
                    'error' => 'No AI providers available',
                ], 503);
            }

            $options = [
                'model' => $request->input('model') ?: $provider['model'],
                'temperature' => $request->input('temperature', 0.7),
                'max_tokens' => $request->input('max_tokens', 1000),
            ];

            $result = $this->aiManager->generateContentPlan(
                $request->input('brief'),
                $request->input('days', 7),
                $options
            );

            if ($result['success']) {
                // Track the generation
                $generation = $this->costService->trackGeneration(
                    companyId: $companyId,
                    userId: $user->id,
                    type: 'plan',
                    provider: $result['provider'],
                    model: $result['model'],
                    prompt: json_encode($request->input('brief')),
                    result: $result['content'],
                    tokensUsed: $result['tokens_used'],
                    cost: $result['cost'],
                    metadata: [
                        'brief' => $request->input('brief'),
                        'days' => $result['days'],
                        'temperature' => $options['temperature'],
                        'max_tokens' => $options['max_tokens'],
                    ]
                );

                return response()->json([
                    'success' => true,
                    'content' => $result['content'],
                    'days' => $result['days'],
                    'brief' => $result['brief'],
                    'provider' => $result['provider'],
                    'model' => $result['model'],
                    'tokens_used' => $result['tokens_used'],
                    'cost' => $result['cost'],
                    'generation_id' => $generation->id,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Content plan generation failed',
                'provider' => $result['provider'] ?? 'unknown',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Content plan generation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'brief' => $request->input('brief'),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Generate general text content
     */
    public function generateText(TextGenerationRequest $request)
    {
        try {
            $user = Auth::user();
            $companyId = $user->current_company_id;

            // Select provider
            $provider = $this->smartSelection->selectTextProvider(
                preferredProvider: $request->input('provider'),
                companyId: $companyId,
                estimatedTokens: $request->input('max_tokens', 1000)
            );

            if (! $provider) {
                return $this->returnResponse([
                    'success' => false,
                    'error' => 'No AI providers available',
                ], 503);
            }

            $options = [
                'provider' => $request->input('provider'),
                'model' => $request->input('model') ?: $provider['model'],
                'temperature' => $request->input('temperature', 0.7),
                'max_tokens' => $request->input('max_tokens', 1000),
            ];

            $result = $this->aiManager->generateText($request->input('prompt'), $options);

            if ($result['success']) {
                // Track the generation
                $generation = $this->costService->trackGeneration(
                    companyId: $companyId,
                    userId: $user->id,
                    type: 'description',
                    provider: $result['provider'],
                    model: $result['model'],
                    prompt: $request->input('prompt'),
                    result: $result['content'],
                    tokensUsed: $result['tokens_used'],
                    cost: $result['cost'],
                    metadata: [
                        'temperature' => $options['temperature'],
                        'max_tokens' => $options['max_tokens'],
                    ]
                );

                return $this->returnResponse([
                    'success' => true,
                    'content' => $result['content'],
                    'provider' => $result['provider'],
                    'model' => $result['model'],
                    'tokens_used' => $result['tokens_used'],
                    'cost' => $result['cost'],
                    'generation_id' => $generation->id,
                ]);
            }

            return $this->returnResponse([
                'success' => false,
                'error' => $result['error'] ?? 'Text generation failed',
                'provider' => $result['provider'] ?? 'unknown',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Text generation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'prompt' => $request->input('prompt'),
            ]);

            return $this->returnResponse([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Moderate text content
     */
    public function moderateText(TextGenerationRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->current_company_id;

            $result = $this->aiManager->moderateContent(
                $request->input('content'),
                $request->input('provider')
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'flagged' => $result['flagged'],
                    'categories' => $result['categories'] ?? [],
                    'category_scores' => $result['category_scores'] ?? [],
                    'provider' => $result['provider'],
                    'safe_for_publication' => ! $result['flagged'],
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Content moderation failed',
                'provider' => $result['provider'] ?? 'unknown',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Text moderation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'content' => $request->input('content'),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }
}
