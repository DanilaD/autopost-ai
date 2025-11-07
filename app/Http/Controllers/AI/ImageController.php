<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\ImageEditRequest;
use App\Http\Requests\AI\ImageGenerationRequest;
use App\Http\Requests\AI\ImageModerationRequest;
use App\Services\AI\Services\AIServiceManager;
use App\Services\AI\Services\CostCalculationService;
use App\Services\AI\Services\SmartProviderSelectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * AI Image Controller
 *
 * Handles image generation tasks including social media images,
 * art creation, and image variations.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class ImageController extends Controller
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
     * Generate a single image
     */
    public function generateImage(ImageGenerationRequest $request)
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            // Check budget limits
            $budgetLimits = $this->costService->checkBudgetLimits($companyId);
            if ($budgetLimits['daily_exceeded'] || $budgetLimits['monthly_exceeded']) {
                return $this->returnResponse([
                    'success' => false,
                    'error' => 'Daily or monthly budget exceeded',
                    'budget_status' => $budgetLimits,
                ], 402);
            }

            // Select provider
            $provider = $this->smartSelection->selectImageProvider(
                preferredProvider: $request->input('provider'),
                companyId: $companyId,
                imageCount: 1
            );

            if (! $provider) {
                return $this->returnResponse([
                    'success' => false,
                    'error' => 'No image generation providers available',
                ], 503);
            }

            $options = [
                'model' => $request->input('model') ?: $provider['provider']['model'],
                'size' => $request->input('size', '1024x1024'),
                'style' => $request->input('style', 'vivid'),
                'quality' => $request->input('quality', 'standard'),
            ];

            $result = $this->aiManager->generateImage($request->input('prompt'), $options);

            if ($result['success']) {
                // Save image to storage
                $imagePath = $this->saveImage($result['image_url'], $user->id, $companyId);

                // Track the generation
                $generation = $this->costService->trackGeneration(
                    companyId: $companyId,
                    userId: $user->id,
                    type: 'image',
                    provider: $result['provider'],
                    model: $result['model'],
                    prompt: $request->input('prompt'),
                    result: $imagePath,
                    tokensUsed: 0,
                    cost: $result['cost'],
                    metadata: [
                        'image_path' => $imagePath,
                        'size' => $options['size'],
                        'style' => $options['style'],
                        'quality' => $options['quality'],
                        'original_url' => $result['image_url'],
                    ]
                );

                return $this->returnResponse([
                    'success' => true,
                    'image_url' => $result['image_url'],
                    'image_path' => $imagePath,
                    'provider' => $result['provider'],
                    'model' => $result['model'],
                    'cost' => $result['cost'],
                    'generation_id' => $generation->id,
                    'budget_status' => $budgetLimits,
                ]);
            }

            return $this->returnResponse([
                'success' => false,
                'error' => $result['error'] ?? 'Image generation failed',
                'provider' => $result['provider'] ?? 'unknown',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Image generation failed', [
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
     * Generate multiple images
     */
    public function generateImages(ImageGenerationRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            // Check budget limits
            $budgetLimits = $this->costService->checkBudgetLimits($companyId);
            $imageCount = $request->input('count', 1);
            $estimatedCost = $this->costService->calculateImageCost('openai', 'dall-e-3', $imageCount);

            if ($budgetLimits['daily_exceeded'] || $budgetLimits['monthly_exceeded']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Daily or monthly budget exceeded',
                    'budget_status' => $budgetLimits,
                ], 402);
            }

            // Select provider
            $provider = $this->smartSelection->selectImageProvider(
                preferredProvider: $request->input('provider'),
                companyId: $companyId,
                imageCount: $imageCount
            );

            if (! $provider) {
                return response()->json([
                    'success' => false,
                    'error' => 'No image generation providers available',
                ], 503);
            }

            $options = [
                'model' => $request->input('model') ?: $provider['provider']['model'],
                'size' => $request->input('size', '1024x1024'),
                'style' => $request->input('style', 'vivid'),
                'quality' => $request->input('quality', 'standard'),
            ];

            $result = $this->aiManager->generateImages($request->input('prompt'), $imageCount, $options);

            if ($result['success']) {
                $imagePaths = [];
                foreach ($result['image_urls'] as $imageUrl) {
                    $imagePaths[] = $this->saveImage($imageUrl, $user->id, $companyId);
                }

                // Track the generation
                $generation = $this->costService->trackGeneration(
                    companyId: $companyId,
                    userId: $user->id,
                    type: 'image',
                    provider: $result['provider'],
                    model: $result['model'],
                    prompt: $request->input('prompt'),
                    result: json_encode($imagePaths),
                    tokensUsed: 0,
                    cost: $result['cost'],
                    metadata: [
                        'image_paths' => $imagePaths,
                        'image_count' => $result['count'],
                        'size' => $options['size'],
                        'style' => $options['style'],
                        'quality' => $options['quality'],
                        'original_urls' => $result['image_urls'],
                    ]
                );

                return response()->json([
                    'success' => true,
                    'image_urls' => $result['image_urls'],
                    'image_paths' => $imagePaths,
                    'count' => $result['count'],
                    'provider' => $result['provider'],
                    'model' => $result['model'],
                    'cost' => $result['cost'],
                    'generation_id' => $generation->id,
                    'budget_status' => $budgetLimits,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Image generation failed',
                'provider' => $result['provider'] ?? 'unknown',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Multiple image generation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'prompt' => $request->input('prompt'),
                'count' => $request->input('count'),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Edit an existing image
     */
    public function editImage(ImageEditRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            // Check budget limits
            $budgetLimits = $this->costService->checkBudgetLimits($companyId);
            if ($budgetLimits['daily_exceeded'] || $budgetLimits['monthly_exceeded']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Daily or monthly budget exceeded',
                    'budget_status' => $budgetLimits,
                ], 402);
            }

            // Store uploaded image temporarily
            $imagePath = $request->file('image')->store('temp', 'local');
            $fullPath = storage_path('app/'.$imagePath);

            $options = [
                'model' => $request->input('model', 'dall-e-2'),
                'size' => $request->input('size', '1024x1024'),
            ];

            $result = $this->aiManager->editImage($fullPath, $request->input('prompt'), $options);

            // Clean up temporary file
            Storage::delete($imagePath);

            if ($result['success']) {
                // Save edited image
                $editedImagePath = $this->saveImage($result['image_url'], $user->id, $companyId);

                // Track the generation
                $generation = $this->costService->trackGeneration(
                    companyId: $companyId,
                    userId: $user->id,
                    type: 'image',
                    provider: $result['provider'],
                    model: $result['model'],
                    prompt: $request->input('prompt'),
                    result: $editedImagePath,
                    tokensUsed: 0,
                    cost: $result['cost'],
                    metadata: [
                        'image_path' => $editedImagePath,
                        'size' => $options['size'],
                        'original_image' => $imagePath,
                        'original_url' => $result['image_url'],
                    ]
                );

                return response()->json([
                    'success' => true,
                    'image_url' => $result['image_url'],
                    'image_path' => $editedImagePath,
                    'provider' => $result['provider'],
                    'model' => $result['model'],
                    'cost' => $result['cost'],
                    'generation_id' => $generation->id,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Image editing failed',
                'provider' => $result['provider'] ?? 'unknown',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Image editing failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'prompt' => $request->input('prompt'),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Generate image variations
     */
    public function generateVariations(ImageEditRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            // Check budget limits
            $budgetLimits = $this->costService->checkBudgetLimits($companyId);
            $variationCount = $request->input('count', 1);

            if ($budgetLimits['daily_exceeded'] || $budgetLimits['monthly_exceeded']) {
                return response()->json([
                    'success' => false,
                    'error' => 'Daily or monthly budget exceeded',
                    'budget_status' => $budgetLimits,
                ], 402);
            }

            // Store uploaded image temporarily
            $imagePath = $request->file('image')->store('temp', 'local');
            $fullPath = storage_path('app/'.$imagePath);

            $options = [
                'model' => $request->input('model', 'dall-e-2'),
                'size' => $request->input('size', '1024x1024'),
            ];

            $result = $this->aiManager->generateVariations($fullPath, $variationCount, $options);

            // Clean up temporary file
            Storage::delete($imagePath);

            if ($result['success']) {
                $variationPaths = [];
                foreach ($result['image_urls'] as $imageUrl) {
                    $variationPaths[] = $this->saveImage($imageUrl, $user->id, $companyId);
                }

                // Track the generation
                $generation = $this->costService->trackGeneration(
                    companyId: $companyId,
                    userId: $user->id,
                    type: 'image',
                    provider: $result['provider'],
                    model: $result['model'],
                    prompt: 'Image variations',
                    result: json_encode($variationPaths),
                    tokensUsed: 0,
                    cost: $result['cost'],
                    metadata: [
                        'image_paths' => $variationPaths,
                        'variation_count' => $result['count'],
                        'size' => $options['size'],
                        'original_image' => $imagePath,
                        'original_urls' => $result['image_urls'],
                    ]
                );

                return response()->json([
                    'success' => true,
                    'image_urls' => $result['image_urls'],
                    'image_paths' => $variationPaths,
                    'count' => $result['count'],
                    'provider' => $result['provider'],
                    'model' => $result['model'],
                    'cost' => $result['cost'],
                    'generation_id' => $generation->id,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Image variation generation failed',
                'provider' => $result['provider'] ?? 'unknown',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Image variation generation failed', [
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
     * Moderate image content
     */
    public function moderateImage(ImageModerationRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            // Store uploaded image temporarily
            $imagePath = $request->file('image')->store('temp', 'local');
            $fullPath = storage_path('app/'.$imagePath);

            $result = $this->aiManager->moderateImage($fullPath, [
                'provider' => $request->input('provider'),
            ]);

            // Clean up temporary file
            Storage::delete($imagePath);

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
                'error' => $result['error'] ?? 'Image moderation failed',
                'provider' => $result['provider'] ?? 'unknown',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Image moderation failed', [
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
     * Save image from URL to storage
     */
    private function saveImage(string $imageUrl, int $userId, int $companyId): string
    {
        try {
            $imageData = file_get_contents($imageUrl);
            $filename = 'ai_images/'.$companyId.'/'.$userId.'/'.uniqid().'.png';

            Storage::disk('public')->put($filename, $imageData);

            return $filename;
        } catch (\Exception $e) {
            Log::error('Failed to save image', [
                'error' => $e->getMessage(),
                'image_url' => $imageUrl,
                'user_id' => $userId,
                'company_id' => $companyId,
            ]);

            return '';
        }
    }
}
