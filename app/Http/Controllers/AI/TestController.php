<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Services\AI\Services\AIServiceManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

/**
 * AI Test Controller
 *
 * This controller provides test endpoints to verify the AI system
 * is working correctly. It's used for development and testing purposes.
 *
 * @version 1.0
 *
 * @since 2025-10-16
 */
class TestController extends Controller
{
    private AIServiceManager $aiServiceManager;

    /**
     * Initialize the controller
     */
    public function __construct(AIServiceManager $aiServiceManager)
    {
        $this->aiServiceManager = $aiServiceManager;
    }

    /**
     * Helper method to return appropriate response based on request type
     */
    private function returnResponse($data, $status = 200)
    {
        if (request()->header('X-Inertia')) {
            // For Inertia requests, return the data directly as props
            return response()->json($data, $status);
        }

        return response()->json($data, $status);
    }

    /**
     * Test AI provider availability
     */
    public function testProviders()
    {
        try {
            $providers = $this->aiServiceManager->getAvailableProviders();
            $stats = $this->aiServiceManager->getProviderStats();

            return $this->returnResponse([
                'success' => true,
                'available_providers' => $providers,
                'provider_stats' => $stats,
                'message' => 'Provider status retrieved successfully',
            ]);

        } catch (\Exception $e) {
            return $this->returnResponse([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Failed to retrieve provider status',
            ], 500);
        }
    }

    /**
     * Test text generation
     */
    public function testTextGeneration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|max:1000',
            'provider' => 'nullable|string|in:openai,anthropic,google,local',
            'model' => 'nullable|string',
            'temperature' => 'nullable|numeric|between:0,2',
            'max_tokens' => 'nullable|integer|min:1|max:4000',
        ]);

        if ($validator->fails()) {
            return $this->returnResponse([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        try {
            $options = $request->only(['provider', 'model', 'temperature', 'max_tokens']);
            $result = $this->aiServiceManager->generateText($request->prompt, $options);

            return $this->returnResponse([
                'success' => $result['success'],
                'data' => $result,
                'message' => $result['success'] ? 'Text generated successfully' : 'Text generation failed',
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            // Provide user-friendly error messages
            $errorMessage = $e->getMessage();
            $userFriendlyMessage = 'Text generation failed';

            if (str_contains($errorMessage, 'credit balance is too low')) {
                $userFriendlyMessage = 'Your Anthropic account has insufficient credits. Please add credits to your account or try a different provider.';
            } elseif (str_contains($errorMessage, 'quota')) {
                $userFriendlyMessage = 'API quota exceeded. Please try again later or use a different provider.';
            } elseif (str_contains($errorMessage, 'invalid_request_error')) {
                $userFriendlyMessage = 'Invalid request. Please check your input and try again.';
            } elseif (str_contains($errorMessage, 'authentication')) {
                $userFriendlyMessage = 'Authentication failed. Please check your API keys.';
            } elseif (str_contains($errorMessage, 'rate limit')) {
                $userFriendlyMessage = 'Rate limit exceeded. Please wait a moment and try again.';
            } elseif (str_contains($errorMessage, 'model not found')) {
                $userFriendlyMessage = 'The selected AI model is not available. Please try a different model or provider.';
            }

            return $this->returnResponse([
                'success' => false,
                'error' => $errorMessage,
                'message' => $userFriendlyMessage,
            ], 500);
        }
    }

    /**
     * Test caption generation
     */
    public function testCaptionGeneration(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:500',
            'style' => 'nullable|string|in:motivational,casual,professional,funny,educational',
            'provider' => 'nullable|string|in:openai,anthropic,google,local',
            'model' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        try {
            $options = $request->only(['provider', 'model']);
            $style = $request->style ?? 'casual';

            $result = $this->aiServiceManager->generateCaption(
                $request->description,
                $style,
                $options
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
                'message' => $result['success'] ? 'Caption generated successfully' : 'Caption generation failed',
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Caption generation failed with exception',
            ], 500);
        }
    }

    /**
     * Test hashtag generation
     */
    public function testHashtagGeneration(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:1000',
            'count' => 'nullable|integer|min:1|max:30',
            'provider' => 'nullable|string|in:openai,anthropic,google,local',
            'model' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        try {
            $options = $request->only(['provider', 'model']);
            $count = $request->count ?? 10;

            $result = $this->aiServiceManager->generateHashtags(
                $request->content,
                $count,
                $options
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
                'message' => $result['success'] ? 'Hashtags generated successfully' : 'Hashtag generation failed',
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Hashtag generation failed with exception',
            ], 500);
        }
    }

    /**
     * Test content plan generation
     */
    public function testContentPlanGeneration(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'brief' => 'required|array',
            'brief.industry' => 'required|string|max:100',
            'brief.target_audience' => 'required|string|max:100',
            'brief.brand_voice' => 'required|string|max:100',
            'brief.goals' => 'nullable|string|max:200',
            'days' => 'nullable|integer|min:1|max:30',
            'provider' => 'nullable|string|in:openai,anthropic,google,local',
            'model' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        try {
            $options = $request->only(['provider', 'model']);
            $days = $request->days ?? 7;

            $result = $this->aiServiceManager->generateContentPlan(
                $request->brief,
                $days,
                $options
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
                'message' => $result['success'] ? 'Content plan generated successfully' : 'Content plan generation failed',
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Content plan generation failed with exception',
            ], 500);
        }
    }

    /**
     * Test image generation
     */
    public function testImageGeneration(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|max:1000',
            'provider' => 'nullable|string|in:openai,anthropic,google,local,auto',
            'model' => 'nullable|string',
            'size' => 'nullable|string|in:256x256,512x512,1024x1024,1024x1792,1792x1024',
            'quality' => 'nullable|string|in:standard,hd',
            'style' => 'nullable|string|in:vivid,natural',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        try {
            $options = $request->only(['provider', 'model', 'size', 'quality', 'style']);

            $result = $this->aiServiceManager->generateImage($request->prompt, $options);

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
                'message' => $result['success'] ? 'Image generated successfully' : 'Image generation failed',
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            // Provide user-friendly error messages
            $errorMessage = $e->getMessage();
            $userFriendlyMessage = 'Image generation failed';

            if (str_contains($errorMessage, 'billing hard limit has been reached')) {
                $userFriendlyMessage = 'Your OpenAI account has reached its billing limit. Please add credits to your account or try a different provider.';
            } elseif (str_contains($errorMessage, 'credit balance is too low')) {
                $userFriendlyMessage = 'Your account has insufficient credits. Please add credits to your account or try a different provider.';
            } elseif (str_contains($errorMessage, 'quota')) {
                $userFriendlyMessage = 'API quota exceeded. Please try again later or use a different provider.';
            } elseif (str_contains($errorMessage, 'invalid_request_error')) {
                $userFriendlyMessage = 'Invalid request. Please check your input and try again.';
            } elseif (str_contains($errorMessage, 'authentication')) {
                $userFriendlyMessage = 'Authentication failed. Please check your API keys.';
            } elseif (str_contains($errorMessage, 'rate limit')) {
                $userFriendlyMessage = 'Rate limit exceeded. Please wait a moment and try again.';
            } elseif (str_contains($errorMessage, 'model not found')) {
                $userFriendlyMessage = 'The selected AI model is not available. Please try a different model or provider.';
            }

            return response()->json([
                'success' => false,
                'error' => $errorMessage,
                'message' => $userFriendlyMessage,
            ], 500);
        }
    }

    /**
     * Test content moderation
     */
    public function testContentModeration(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:2000',
            'type' => 'nullable|string|in:text,image,video',
            'provider' => 'nullable|string|in:openai,anthropic,google,local',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        try {
            $options = $request->only(['provider']);
            $type = $request->type ?? 'text';

            $result = $this->aiServiceManager->moderateContent(
                $request->content,
                $type,
                $options
            );

            return response()->json([
                'success' => $result['success'],
                'data' => $result,
                'message' => $result['success'] ? 'Content moderation completed' : 'Content moderation failed',
            ], $result['success'] ? 200 : 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Content moderation failed with exception',
            ], 500);
        }
    }

    /**
     * Test content safety check
     */
    public function testContentSafety(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:2000',
            'type' => 'nullable|string|in:text,image,video',
            'provider' => 'nullable|string|in:openai,anthropic,google,local',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        try {
            $options = $request->only(['provider']);
            $type = $request->type ?? 'text';

            $isSafe = $this->aiServiceManager->isContentSafe(
                $request->content,
                $type,
                $options
            );

            return response()->json([
                'success' => true,
                'is_safe' => $isSafe,
                'content_type' => $type,
                'message' => $isSafe ? 'Content is safe for publication' : 'Content may violate policies',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'message' => 'Content safety check failed with exception',
            ], 500);
        }
    }

    /**
     * Test chat functionality
     */
    public function testChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000',
            'provider' => 'nullable|string|in:openai,anthropic,google,local',
        ]);

        if ($validator->fails()) {
            return $this->returnResponse([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $provider = $request->input('provider', 'local');
            $message = $request->input('message');

            $result = $this->aiServiceManager->generateText(
                $message,
                [
                    'provider' => $provider,
                    'temperature' => 0.7,
                    'max_tokens' => 150,
                ]
            );

            return $this->returnResponse([
                'success' => $result['success'] ?? false,
                'data' => $result,
                'message' => ($result['success'] ?? false) ? 'Chat test completed successfully' : 'Chat test failed',
            ], ($result['success'] ?? false) ? 200 : 400);

        } catch (\Exception $e) {
            return $this->returnResponse([
                'success' => false,
                'message' => 'Chat test failed: '.$e->getMessage(),
            ], 500);
        }
    }
}
