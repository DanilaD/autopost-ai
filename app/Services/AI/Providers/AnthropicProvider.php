<?php

namespace App\Services\AI\Providers;

use App\Enums\AIProvider;
use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Contracts\TextGenerationInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Anthropic Provider Implementation
 *
 * This provider implements Anthropic's Claude models for text generation
 * with advanced reasoning capabilities and long context windows.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class AnthropicProvider implements AIProviderInterface, TextGenerationInterface
{
    private ?string $apiKey;

    private ?string $baseUrl;

    private int $timeout;

    private int $maxRetries;

    /**
     * Initialize the Anthropic provider
     */
    public function __construct()
    {
        $this->apiKey = config('ai.anthropic.key');
        $this->baseUrl = config('ai.anthropic.base');
        $this->timeout = config('ai.anthropic.timeout', 30);
        $this->maxRetries = config('ai.anthropic.max_retries', 3);
    }

    /**
     * Get the provider name
     */
    public function getName(): string
    {
        return AIProvider::ANTHROPIC->value;
    }

    /**
     * Check if the provider is available and properly configured
     */
    public function isAvailable(): bool
    {
        return ! empty($this->apiKey) && $this->testConnection();
    }

    /**
     * Get the cost per token for text generation
     */
    public function getCostPerToken(string $model): float
    {
        $models = config('ai.anthropic.models.text', []);

        return $models[$model]['cost_per_token'] ?? 0.0;
    }

    /**
     * Get the cost per image for image generation
     */
    public function getCostPerImage(string $model): float
    {
        // Anthropic doesn't support image generation
        return 0.0;
    }

    /**
     * Get available models for this provider
     */
    public function getAvailableModels(): array
    {
        $textModels = array_keys(config('ai.anthropic.models.text', []));

        return [
            'text' => $textModels,
            'image' => [], // Anthropic doesn't support image generation
        ];
    }

    /**
     * Test the provider connection
     */
    public function testConnection(): bool
    {
        // If API key is present, assume connection is available
        // The actual API call will handle authentication errors
        return ! empty($this->apiKey);
    }

    /**
     * Generate text content based on a prompt
     */
    public function generateText(string $prompt, array $options = []): array
    {
        $model = $options['model'] ?? 'claude-3-haiku-20240307';
        $maxTokens = $options['max_tokens'] ?? config('ai.defaults.max_tokens', 1000);
        $temperature = $options['temperature'] ?? config('ai.defaults.temperature', 0.7);

        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->maxRetries, 1000)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'anthropic-version' => '2023-06-01',
                ])
                ->post($this->baseUrl.'messages', [
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'temperature' => $temperature,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['content'][0]['text'] ?? '';
                $tokensUsed = ($data['usage']['input_tokens'] ?? 0) + ($data['usage']['output_tokens'] ?? 0);

                return [
                    'success' => true,
                    'content' => $content,
                    'tokens_used' => $tokensUsed,
                    'model' => $model,
                    'provider' => $this->getName(),
                    'cost' => $this->getCostPerToken($model) * $tokensUsed,
                    'metadata' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => 'Anthropic API request failed',
                'status_code' => $response->status(),
                'response' => $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Anthropic text generation failed', [
                'error' => $e->getMessage(),
                'prompt' => substr($prompt, 0, 100),
                'model' => $model,
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $this->getName(),
            ];
        }
    }

    /**
     * Generate text with streaming support
     */
    public function generateTextStream(string $prompt, array $options = []): \Generator
    {
        $model = $options['model'] ?? 'claude-3-haiku-20240307';
        $maxTokens = $options['max_tokens'] ?? config('ai.defaults.max_tokens', 1000);
        $temperature = $options['temperature'] ?? config('ai.defaults.temperature', 0.7);

        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->maxRetries, 1000)
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'Content-Type' => 'application/json',
                    'anthropic-version' => '2023-06-01',
                ])
                ->post($this->baseUrl.'messages', [
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'temperature' => $temperature,
                    'stream' => true,
                    'messages' => [
                        ['role' => 'user', 'content' => $prompt],
                    ],
                ]);

            if ($response->successful()) {
                $stream = $response->getBody();

                while (! $stream->eof()) {
                    $line = $stream->readLine();
                    if (strpos($line, 'data: ') === 0) {
                        $data = substr($line, 6);
                        if ($data === '[DONE]') {
                            break;
                        }

                        $decoded = json_decode($data, true);
                        if ($decoded && isset($decoded['delta']['text'])) {
                            yield [
                                'content' => $decoded['delta']['text'],
                                'done' => false,
                            ];
                        }
                    }
                }

                yield ['done' => true];
            } else {
                yield [
                    'error' => 'Anthropic streaming request failed',
                    'status_code' => $response->status(),
                ];
            }

        } catch (\Exception $e) {
            Log::error('Anthropic streaming failed', [
                'error' => $e->getMessage(),
                'prompt' => substr($prompt, 0, 100),
                'model' => $model,
            ]);

            yield [
                'error' => $e->getMessage(),
                'provider' => $this->getName(),
            ];
        }
    }

    /**
     * Generate a caption for social media content
     */
    public function generateCaption(string $description, string $style = 'casual', array $options = []): array
    {
        $prompt = $this->buildCaptionPrompt($description, $style);
        $result = $this->generateText($prompt, $options);

        if ($result['success']) {
            $result['type'] = 'caption';
            $result['style'] = $style;
        }

        return $result;
    }

    /**
     * Generate hashtags for content
     */
    public function generateHashtags(string $content, int $count = 10, array $options = []): array
    {
        $prompt = "Generate {$count} relevant hashtags for this content: {$content}";
        $result = $this->generateText($prompt, $options);

        if ($result['success']) {
            $hashtags = $this->extractHashtags($result['content']);
            $result['hashtags'] = array_slice($hashtags, 0, $count);
            $result['type'] = 'hashtags';
        }

        return $result;
    }

    /**
     * Generate content plan for social media
     */
    public function generateContentPlan(array $brief, int $days = 7, array $options = []): array
    {
        $prompt = $this->buildContentPlanPrompt($brief, $days);
        $result = $this->generateText($prompt, $options);

        if ($result['success']) {
            $result['type'] = 'plan';
            $result['days'] = $days;
            $result['brief'] = $brief;
        }

        return $result;
    }

    /**
     * Generate an image based on a text prompt
     */
    public function generateImage(string $prompt, array $options = []): array
    {
        // Anthropic doesn't support image generation
        return [
            'success' => false,
            'error' => 'Image generation not supported by Anthropic',
            'provider' => $this->getName(),
        ];
    }

    /**
     * Generate multiple images based on a text prompt
     */
    public function generateImages(string $prompt, int $count = 1, array $options = []): array
    {
        // Anthropic doesn't support image generation
        return [
            'success' => false,
            'error' => 'Image generation not supported by Anthropic',
            'provider' => $this->getName(),
        ];
    }

    /**
     * Edit an existing image based on a prompt
     */
    public function editImage(string $imagePath, string $prompt, array $options = []): array
    {
        // Anthropic doesn't support image editing
        return [
            'success' => false,
            'error' => 'Image editing not supported by Anthropic',
            'provider' => $this->getName(),
        ];
    }

    /**
     * Generate image variations
     */
    public function generateVariations(string $imagePath, int $count = 1, array $options = []): array
    {
        // Anthropic doesn't support image variations
        return [
            'success' => false,
            'error' => 'Image variations not supported by Anthropic',
            'provider' => $this->getName(),
        ];
    }

    /**
     * Check if image generation is supported by this provider
     */
    public function supportsImageGeneration(): bool
    {
        return false;
    }

    /**
     * Moderate text content for policy violations
     */
    public function moderateText(string $content, array $options = []): array
    {
        // Anthropic doesn't have a separate moderation API
        // We can use Claude's built-in safety features by asking it to evaluate content
        try {
            $prompt = "Please evaluate this content for safety and policy violations. Respond with 'SAFE' if appropriate, or 'UNSAFE' with explanation if it violates policies:\n\n{$content}";

            $result = $this->generateText($prompt, ['max_tokens' => 100]);

            if ($result['success']) {
                $response = strtoupper(trim($result['content']));
                $flagged = ! str_starts_with($response, 'SAFE');

                return [
                    'success' => true,
                    'flagged' => $flagged,
                    'categories' => $flagged ? ['policy_violation'] : [],
                    'category_scores' => $flagged ? ['policy_violation' => 0.8] : [],
                    'provider' => $this->getName(),
                    'metadata' => ['response' => $result['content']],
                ];
            }

            return [
                'success' => false,
                'error' => 'Anthropic moderation failed',
                'provider' => $this->getName(),
            ];

        } catch (\Exception $e) {
            Log::error('Anthropic moderation failed', [
                'error' => $e->getMessage(),
                'content' => substr($content, 0, 100),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'provider' => $this->getName(),
            ];
        }
    }

    /**
     * Moderate image content for policy violations
     */
    public function moderateImage(string $imagePath, array $options = []): array
    {
        // Anthropic doesn't support image moderation
        return [
            'success' => false,
            'error' => 'Image moderation not supported by Anthropic',
            'provider' => $this->getName(),
        ];
    }

    /**
     * Check if content is safe for publication
     */
    public function isContentSafe(string $content, string $type = 'text', array $options = []): bool
    {
        if ($type !== 'text') {
            return true; // Assume safe for non-text content
        }

        $result = $this->moderateText($content, $options);

        return $result['success'] && ! ($result['flagged'] ?? false);
    }

    /**
     * Get detailed moderation analysis
     */
    public function getModerationAnalysis(string $content, string $type = 'text', array $options = []): array
    {
        if ($type !== 'text') {
            return [
                'success' => true,
                'flagged' => false,
                'categories' => [],
                'category_scores' => [],
                'provider' => $this->getName(),
            ];
        }

        return $this->moderateText($content, $options);
    }

    /**
     * Check if moderation is supported by this provider
     */
    public function supportsModeration(): bool
    {
        return true; // Claude has built-in safety features
    }

    /**
     * Build caption prompt based on description and style
     */
    private function buildCaptionPrompt(string $description, string $style): string
    {
        $styleInstructions = match ($style) {
            'motivational' => 'Write an inspiring and motivational caption that encourages action and positive thinking.',
            'casual' => 'Write a casual, friendly caption that feels natural and conversational.',
            'professional' => 'Write a professional caption suitable for business or corporate content.',
            'funny' => 'Write a humorous and entertaining caption that will make people smile.',
            'educational' => 'Write an informative caption that teaches something valuable to the audience.',
            default => 'Write an engaging caption that matches the content.',
        };

        return "{$styleInstructions}\n\nContent description: {$description}\n\nGenerate a social media caption (1-2 sentences, include relevant emojis):";
    }

    /**
     * Build content plan prompt based on brief and days
     */
    private function buildContentPlanPrompt(array $brief, int $days): string
    {
        $industry = $brief['industry'] ?? 'general';
        $audience = $brief['target_audience'] ?? 'general audience';
        $voice = $brief['brand_voice'] ?? 'professional';
        $goals = $brief['goals'] ?? 'engage and inform';

        return "Create a {$days}-day content plan for a {$industry} brand targeting {$audience}.\n\n".
               "Brand voice: {$voice}\n".
               "Goals: {$goals}\n\n".
               "For each day, provide:\n".
               "1. Content theme/topic\n".
               "2. Post type (image, video, carousel, etc.)\n".
               "3. Caption idea\n".
               "4. Relevant hashtags\n".
               "5. Call-to-action suggestion\n\n".
               'Format as a structured plan with clear daily sections.';
    }

    /**
     * Extract hashtags from generated content
     */
    private function extractHashtags(string $content): array
    {
        preg_match_all('/#\w+/', $content, $matches);

        return array_unique($matches[0]);
    }
}
