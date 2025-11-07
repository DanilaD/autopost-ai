<?php

namespace App\Http\Controllers\AI;

use App\Http\Controllers\Controller;
use App\Http\Requests\AI\ChatRequest;
use App\Services\AI\Services\AIServiceManager;
use App\Services\AI\Services\CostCalculationService;
use App\Services\AI\Services\SmartProviderSelectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * AI Chat Controller
 *
 * Handles conversational AI interactions with multi-provider support.
 * Provides chat endpoints for real-time AI conversations.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class ChatController extends Controller
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
     * Start a new chat session
     */
    public function startChat(ChatRequest $request)
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            // Check budget limits
            $budgetLimits = $this->costService->checkBudgetLimits($companyId);
            if ($budgetLimits['daily_exceeded'] || $budgetLimits['monthly_exceeded']) {
                // Force use of free providers when budget exceeded
                $provider = $this->smartSelection->selectTextProvider(
                    preferredProvider: $request->input('provider'),
                    companyId: $companyId,
                    prioritizeFree: true,
                    estimatedTokens: $request->input('max_tokens', 1000)
                );
            } else {
                $provider = $this->smartSelection->selectTextProvider(
                    preferredProvider: $request->input('provider'),
                    companyId: $companyId,
                    estimatedTokens: $request->input('max_tokens', 1000)
                );
            }

            if (! $provider) {
                return $this->returnResponse([
                    'success' => false,
                    'error' => 'No AI providers available',
                ], 503);
            }

            $options = [
                'model' => $request->input('model') ?: $provider['provider']['model'],
                'temperature' => $request->input('temperature', 0.7),
                'max_tokens' => $request->input('max_tokens', 1000),
            ];

            $result = $this->aiManager->generateText($request->input('message'), $options);

            if ($result['success']) {
                // Track the generation
                $generation = $this->costService->trackGeneration(
                    companyId: $companyId,
                    userId: $user->id,
                    type: 'chat',
                    provider: $result['provider'],
                    model: $result['model'],
                    prompt: $request->input('message'),
                    result: $result['content'],
                    tokensUsed: $result['tokens_used'],
                    cost: $result['cost'],
                    metadata: [
                        'temperature' => $options['temperature'],
                        'max_tokens' => $options['max_tokens'],
                        'session_id' => uniqid('chat_'),
                    ]
                );

                return $this->returnResponse([
                    'success' => true,
                    'response' => $result['content'],
                    'provider' => $result['provider'],
                    'model' => $result['model'],
                    'tokens_used' => $result['tokens_used'],
                    'cost' => $result['cost'],
                    'generation_id' => $generation->id,
                    'session_id' => $generation->metadata['session_id'],
                    'budget_status' => $budgetLimits,
                ]);
            }

            return $this->returnResponse([
                'success' => false,
                'error' => $result['error'] ?? 'Text generation failed',
                'provider' => $result['provider'] ?? 'unknown',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Chat generation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request' => $request->all(),
            ]);

            return $this->returnResponse([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Continue a chat session
     */
    public function continueChat(ChatRequest $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

            // Get previous messages from session
            $previousMessages = $this->getChatHistory($request->input('session_id'), $user->id);

            // Build context-aware prompt
            $contextPrompt = $this->buildContextPrompt($previousMessages, $request->input('message'));

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
                'model' => $request->input('model') ?: $provider['provider']['model'],
                'temperature' => $request->input('temperature', 0.7),
                'max_tokens' => $request->input('max_tokens', 1000),
            ];

            $result = $this->aiManager->generateText($contextPrompt, $options);

            if ($result['success']) {
                // Track the generation
                $generation = $this->costService->trackGeneration(
                    companyId: $companyId,
                    userId: $user->id,
                    type: 'chat',
                    provider: $result['provider'],
                    model: $result['model'],
                    prompt: $contextPrompt,
                    result: $result['content'],
                    tokensUsed: $result['tokens_used'],
                    cost: $result['cost'],
                    metadata: [
                        'temperature' => $options['temperature'],
                        'max_tokens' => $options['max_tokens'],
                        'session_id' => $request->input('session_id'),
                        'message_count' => count($previousMessages) + 1,
                    ]
                );

                return response()->json([
                    'success' => true,
                    'content' => $result['content'],
                    'provider' => $result['provider'],
                    'model' => $result['model'],
                    'tokens_used' => $result['tokens_used'],
                    'cost' => $result['cost'],
                    'generation_id' => $generation->id,
                    'session_id' => $request->input('session_id'),
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Text generation failed',
                'provider' => $result['provider'] ?? 'unknown',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Chat continuation failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'session_id' => $request->input('session_id'),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * Stream chat response
     */
    public function streamChat(ChatRequest $request)
    {
        try {
            $user = Auth::user();
            $companyId = $user->company_id;

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
                'model' => $request->input('model') ?: $provider['provider']['model'],
                'temperature' => $request->input('temperature', 0.7),
                'max_tokens' => $request->input('max_tokens', 1000),
            ];

            return response()->stream(function () use ($request, $options, $provider, $companyId, $user) {
                $fullContent = '';
                $tokensUsed = 0;

                foreach ($this->aiManager->generateTextStream($request->input('message'), $options) as $chunk) {
                    if (isset($chunk['error'])) {
                        echo 'data: '.json_encode([
                            'success' => false,
                            'error' => $chunk['error'],
                        ])."\n\n";
                        break;
                    }

                    if (isset($chunk['content'])) {
                        $fullContent .= $chunk['content'];
                        echo 'data: '.json_encode([
                            'success' => true,
                            'content' => $chunk['content'],
                            'done' => $chunk['done'] ?? false,
                        ])."\n\n";
                    }

                    if ($chunk['done'] ?? false) {
                        // Track the complete generation
                        $this->costService->trackGeneration(
                            companyId: $companyId,
                            userId: $user->id,
                            type: 'chat',
                            provider: $provider['provider']['provider'],
                            model: $provider['provider']['model'],
                            prompt: $request->input('message'),
                            result: $fullContent,
                            tokensUsed: $this->estimateTokens($fullContent),
                            cost: 0, // Will be calculated properly
                            metadata: [
                                'streaming' => true,
                                'temperature' => $options['temperature'],
                                'max_tokens' => $options['max_tokens'],
                            ]
                        );
                        break;
                    }
                }
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
            ]);

        } catch (\Exception $e) {
            Log::error('Chat streaming failed', [
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
     * Get chat history for a session
     */
    private function getChatHistory(string $sessionId, int $userId): array
    {
        // This would typically query the database for previous messages
        // For now, return empty array
        return [];
    }

    /**
     * Build context-aware prompt from chat history
     */
    private function buildContextPrompt(array $history, string $newMessage): string
    {
        if (empty($history)) {
            return $newMessage;
        }

        $context = "Previous conversation:\n";
        foreach ($history as $message) {
            $context .= 'User: '.$message['user']."\n";
            $context .= 'Assistant: '.$message['assistant']."\n\n";
        }

        $context .= 'Current message: '.$newMessage;

        return $context;
    }

    /**
     * Estimate token count (rough approximation)
     */
    private function estimateTokens(string $text): int
    {
        return (int) ceil(strlen($text) / 4);
    }
}
