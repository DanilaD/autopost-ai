<?php

use App\Http\Controllers\AI\AnalyticsController;
use App\Http\Controllers\AI\ChatController;
use App\Http\Controllers\AI\ImageController;
use App\Http\Controllers\AI\TextController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AI API Routes
|--------------------------------------------------------------------------
|
| These routes handle all AI-related API endpoints including:
| - Chat functionality
| - Text generation (captions, hashtags, content plans)
| - Image generation and editing
| - Analytics and usage tracking
|
*/

Route::middleware(['auth:web', 'verified'])->prefix('ai')->name('api.ai.')->group(function () {

    // Chat Routes
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::post('/start', [ChatController::class, 'startChat'])->name('start');
        Route::post('/continue', [ChatController::class, 'continueChat'])->name('continue');
        Route::post('/stream', [ChatController::class, 'streamChat'])->name('stream');
    });

    // Text Generation Routes
    Route::prefix('text')->name('text.')->group(function () {
        Route::post('/caption', [TextController::class, 'generateCaption'])->name('caption');
        Route::post('/hashtags', [TextController::class, 'generateHashtags'])->name('hashtags');
        Route::post('/plan', [TextController::class, 'generateContentPlan'])->name('plan');
        Route::post('/generate', [TextController::class, 'generateText'])->name('generate');
        Route::post('/moderate', [TextController::class, 'moderateText'])->name('moderate');
    });

    // Image Generation Routes
    Route::prefix('image')->name('image.')->group(function () {
        Route::post('/generate', [ImageController::class, 'generateImage'])->name('generate');
        Route::post('/generate-multiple', [ImageController::class, 'generateImages'])->name('generate-multiple');
        Route::post('/edit', [ImageController::class, 'editImage'])->name('edit');
        Route::post('/variations', [ImageController::class, 'generateVariations'])->name('variations');
        Route::post('/moderate', [ImageController::class, 'moderateImage'])->name('moderate');
    });

    // Analytics Routes
    Route::prefix('analytics')->name('analytics.')->group(function () {
        Route::get('/company-usage', [AnalyticsController::class, 'getCompanyUsage'])->name('company-usage');
        Route::get('/user-usage', [AnalyticsController::class, 'getUserUsage'])->name('user-usage');
        Route::get('/budget-status', [AnalyticsController::class, 'getBudgetStatus'])->name('budget-status');
        Route::get('/cost-comparison', [AnalyticsController::class, 'getCostComparison'])->name('cost-comparison');
        Route::get('/provider-efficiency', [AnalyticsController::class, 'getProviderEfficiency'])->name('provider-efficiency');
        Route::get('/recommendations', [AnalyticsController::class, 'getOptimizationRecommendations'])->name('recommendations');
        Route::get('/recent-generations', [AnalyticsController::class, 'getRecentGenerations'])->name('recent-generations');
        Route::get('/provider-stats', [AnalyticsController::class, 'getProviderStats'])->name('provider-stats');
        Route::get('/fallback-chain', [AnalyticsController::class, 'getFallbackChain'])->name('fallback-chain');
    });
});
