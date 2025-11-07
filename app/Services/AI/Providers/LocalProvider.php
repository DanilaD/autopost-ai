<?php

namespace App\Services\AI\Providers;

use App\Enums\AIProvider;
use App\Services\AI\Contracts\AIProviderInterface;
use App\Services\AI\Contracts\TextGenerationInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Local AI Provider Implementation (Ollama)
 *
 * This provider implements local AI models via Ollama for free,
 * privacy-focused AI processing. Supports Llama 2, CodeLlama, Mistral, etc.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class LocalProvider implements AIProviderInterface, TextGenerationInterface
{
    private ?string $baseUrl;

    private int $timeout;

    private int $maxRetries;

    private array $availableModels = [];

    /**
     * Initialize the Local AI provider
     */
    public function __construct()
    {
        $this->baseUrl = config('ai.local.base');
        $this->timeout = config('ai.local.timeout', 60);
        $this->maxRetries = config('ai.local.max_retries', 2);
        $this->loadAvailableModels();
    }

    /**
     * Get the provider name
     */
    public function getName(): string
    {
        return AIProvider::LOCAL->value;
    }

    /**
     * Check if the provider is available and properly configured
     */
    public function isAvailable(): bool
    {
        return ! empty($this->baseUrl) && $this->testConnection();
    }

    /**
     * Get the cost per token for text generation
     */
    public function getCostPerToken(string $model): float
    {
        // Local AI is always free
        return 0.0;
    }

    /**
     * Get the cost per image for image generation
     */
    public function getCostPerImage(string $model): float
    {
        // Local AI doesn't support image generation
        return 0.0;
    }

    /**
     * Get available models for this provider
     */
    public function getAvailableModels(): array
    {
        return [
            'text' => $this->availableModels,
            'image' => [], // Local AI doesn't support image generation
        ];
    }

    /**
     * Test the provider connection
     */
    public function testConnection(): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl.'/api/tags');

            if ($response->successful()) {
                $data = $response->json();
                $this->availableModels = array_column($data['models'] ?? [], 'name');

                return true;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Local AI connection test failed', [
                'error' => $e->getMessage(),
                'provider' => $this->getName(),
                'base_url' => $this->baseUrl,
            ]);

            return false;
        }
    }

    /**
     * Load available models from Ollama
     */
    private function loadAvailableModels(): void
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get($this->baseUrl.'/api/tags');

            if ($response->successful()) {
                $data = $response->json();
                $this->availableModels = array_column($data['models'] ?? [], 'name');
            }
        } catch (\Exception $e) {
            Log::warning('Failed to load Ollama models', [
                'error' => $e->getMessage(),
                'base_url' => $this->baseUrl,
            ]);
            // Fallback to default models
            $this->availableModels = ['llama2', 'codellama', 'mistral'];
        }
    }

    /**
     * Generate text content based on a prompt
     */
    public function generateText(string $prompt, array $options = []): array
    {
        $model = $options['model'] ?? $this->getDefaultModel();
        $temperature = $options['temperature'] ?? config('ai.defaults.temperature', 0.7);
        $maxTokens = $options['max_tokens'] ?? config('ai.defaults.max_tokens', 1000);

        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->maxRetries, 1000)
                ->post($this->baseUrl.'/api/generate', [
                    'model' => $model,
                    'prompt' => $prompt,
                    'stream' => false,
                    'options' => [
                        'temperature' => $temperature,
                        'num_predict' => $maxTokens,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['response'] ?? '';
                $tokensUsed = $this->estimateTokens($prompt.$content);

                return [
                    'success' => true,
                    'content' => $content,
                    'tokens_used' => $tokensUsed,
                    'model' => $model,
                    'provider' => $this->getName(),
                    'cost' => 0.0, // Always free
                    'metadata' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => 'Local AI API request failed',
                'status_code' => $response->status(),
                'response' => $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Local AI text generation failed', [
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
        $model = $options['model'] ?? $this->getDefaultModel();
        $temperature = $options['temperature'] ?? config('ai.defaults.temperature', 0.7);
        $maxTokens = $options['max_tokens'] ?? config('ai.defaults.max_tokens', 1000);

        try {
            $response = Http::timeout($this->timeout)
                ->retry($this->maxRetries, 1000)
                ->post($this->baseUrl.'/api/generate', [
                    'model' => $model,
                    'prompt' => $prompt,
                    'stream' => true,
                    'options' => [
                        'temperature' => $temperature,
                        'num_predict' => $maxTokens,
                    ],
                ]);

            if ($response->successful()) {
                $stream = $response->getBody();

                while (! $stream->eof()) {
                    $line = $stream->readLine();
                    if (trim($line)) {
                        $decoded = json_decode($line, true);
                        if ($decoded && isset($decoded['response'])) {
                            yield [
                                'content' => $decoded['response'],
                                'done' => $decoded['done'] ?? false,
                            ];

                            if ($decoded['done'] ?? false) {
                                break;
                            }
                        }
                    }
                }

                yield ['done' => true];
            } else {
                yield [
                    'error' => 'Local AI streaming request failed',
                    'status_code' => $response->status(),
                ];
            }

        } catch (\Exception $e) {
            Log::error('Local AI streaming failed', [
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
        // Local AI doesn't support image generation
        return [
            'success' => false,
            'error' => 'Image generation not supported by Local AI',
            'provider' => $this->getName(),
        ];
    }

    /**
     * Generate multiple images based on a text prompt
     */
    public function generateImages(string $prompt, int $count = 1, array $options = []): array
    {
        // Local AI doesn't support image generation
        return [
            'success' => false,
            'error' => 'Image generation not supported by Local AI',
            'provider' => $this->getName(),
        ];
    }

    /**
     * Edit an existing image based on a prompt
     */
    public function editImage(string $imagePath, string $prompt, array $options = []): array
    {
        // Local AI doesn't support image editing
        return [
            'success' => false,
            'error' => 'Image editing not supported by Local AI',
            'provider' => $this->getName(),
        ];
    }

    /**
     * Generate image variations
     */
    public function generateVariations(string $imagePath, int $count = 1, array $options = []): array
    {
        // Local AI doesn't support image variations
        return [
            'success' => false,
            'error' => 'Image variations not supported by Local AI',
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
                    'category_scores' => $flagged ? ['policy_violation' => 0.7] : [],
                    'provider' => $this->getName(),
                    'metadata' => ['response' => $result['content']],
                ];
            }

            return [
                'success' => false,
                'error' => 'Local AI moderation failed',
                'provider' => $this->getName(),
            ];

        } catch (\Exception $e) {
            Log::error('Local AI moderation failed', [
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
        // Local AI doesn't support image moderation
        return [
            'success' => false,
            'error' => 'Image moderation not supported by Local AI',
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
        return true; // Local AI can do basic moderation
    }

    /**
     * Get the default model to use
     */
    private function getDefaultModel(): string
    {
        // Prefer smaller, faster models for better performance
        $preferredModels = ['llama2:7b', 'mistral:7b', 'llama2', 'mistral'];

        foreach ($preferredModels as $model) {
            if (in_array($model, $this->availableModels)) {
                return $model;
            }
        }

        // Fallback to first available model
        return $this->availableModels[0] ?? 'llama2';
    }

    /**
     * Estimate token count (rough approximation)
     */
    private function estimateTokens(string $text): int
    {
        // Rough estimation: ~4 characters per token
        return (int) ceil(strlen($text) / 4);
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
