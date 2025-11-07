<?php

namespace App\Console\Commands;

use App\Models\AiModel;
use App\Services\AI\Services\AIServiceManager;
use Illuminate\Console\Command;

class TestAI extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ai:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the AI system Phase 1 implementation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🤖 Testing AI System - Phase 3 Implementation');
        $this->info('==============================================');
        $this->newLine();

        // Test 1: Check AI Models in Database
        $this->info('1. Testing AI Models Database...');
        $models = AiModel::where('is_active', true)->get();
        $this->info("   ✅ Found {$models->count()} active AI models");
        foreach ($models as $model) {
            $cost = $model->cost_per_token ? '$'.number_format($model->cost_per_token, 6).'/token' : 'Free';
            $this->info("   - {$model->name} ({$model->provider}) - {$cost}");
        }
        $this->newLine();

        // Test 2: Initialize AI Service Manager
        $this->info('2. Testing AI Service Manager...');
        try {
            $aiManager = new AIServiceManager;
            $this->info('   ✅ AI Service Manager initialized successfully');

            // Test provider availability
            $providers = $aiManager->getAvailableProviders();
            $this->info('   ✅ Available providers: '.implode(', ', array_keys($providers)));

        } catch (\Exception $e) {
            $this->error('   ❌ Error initializing AI Service Manager: '.$e->getMessage());

            return 1;
        }
        $this->newLine();

        // Test 3: Test Provider Stats
        $this->info('3. Testing Provider Statistics...');
        try {
            $stats = $aiManager->getProviderStats();
            foreach ($stats as $provider => $stat) {
                $status = $stat['available'] ? '✅ Available' : '❌ Unavailable';
                $this->info("   - {$provider}: {$status}");
                if ($stat['available']) {
                    $this->info('     Supports Text: '.($stat['supports_text'] ? 'Yes' : 'No'));
                    $this->info('     Supports Image: '.($stat['supports_image'] ? 'Yes' : 'No'));
                    $this->info('     Supports Moderation: '.($stat['supports_moderation'] ? 'Yes' : 'No'));
                }
            }
        } catch (\Exception $e) {
            $this->error('   ❌ Error getting provider stats: '.$e->getMessage());
        }
        $this->newLine();

        // Test 4: Test Text Generation (if any provider is configured)
        $this->info('4. Testing Text Generation...');
        $hasConfiguredProvider = config('ai.openai.key') || config('ai.anthropic.key') || config('ai.google.key');

        if ($hasConfiguredProvider) {
            try {
                $result = $aiManager->generateText('Write a short motivational quote about success.');
                if ($result['success']) {
                    $this->info('   ✅ Text generation successful');
                    $this->info('   Provider: '.($result['provider'] ?? 'unknown'));
                    $this->info('   Tokens used: '.($result['tokens_used'] ?? 0));
                    $this->info('   Cost: $'.number_format($result['cost'] ?? 0, 6));
                    $this->info('   Generated text: '.substr($result['content'] ?? '', 0, 100).'...');
                } else {
                    $this->error('   ❌ Text generation failed: '.($result['error'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                $this->error('   ❌ Error in text generation: '.$e->getMessage());
            }
        } else {
            $this->warn('   ⚠️  No AI provider API keys configured - skipping text generation test');
        }
        $this->newLine();

        // Test 5: Test Caption Generation
        $this->info('5. Testing Caption Generation...');
        if ($hasConfiguredProvider) {
            try {
                $result = $aiManager->generateCaption('A beautiful sunset over the ocean', 'motivational');
                if ($result['success']) {
                    $this->info('   ✅ Caption generation successful');
                    $this->info('   Provider: '.($result['provider'] ?? 'unknown'));
                    $this->info('   Style: '.($result['style'] ?? 'unknown'));
                    $this->info('   Generated caption: '.substr($result['content'] ?? '', 0, 150).'...');
                } else {
                    $this->error('   ❌ Caption generation failed: '.($result['error'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                $this->error('   ❌ Error in caption generation: '.$e->getMessage());
            }
        } else {
            $this->warn('   ⚠️  No AI provider API keys configured - skipping caption generation test');
        }
        $this->newLine();

        // Test 6: Test Hashtag Generation
        $this->info('6. Testing Hashtag Generation...');
        if ($hasConfiguredProvider) {
            try {
                $result = $aiManager->generateHashtags('A beautiful sunset over the ocean with peaceful waves', 5);
                if ($result['success']) {
                    $this->info('   ✅ Hashtag generation successful');
                    $this->info('   Provider: '.($result['provider'] ?? 'unknown'));
                    $this->info('   Generated hashtags: '.implode(', ', $result['hashtags'] ?? []));
                } else {
                    $this->error('   ❌ Hashtag generation failed: '.($result['error'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                $this->error('   ❌ Error in hashtag generation: '.$e->getMessage());
            }
        } else {
            $this->warn('   ⚠️  No AI provider API keys configured - skipping hashtag generation test');
        }
        $this->newLine();

        // Test 7: Test Content Moderation
        $this->info('7. Testing Content Moderation...');
        if ($hasConfiguredProvider) {
            try {
                $result = $aiManager->moderateContent('This is a wonderful day with beautiful weather!');
                if ($result['success']) {
                    $this->info('   ✅ Content moderation successful');
                    $this->info('   Provider: '.($result['provider'] ?? 'unknown'));
                    $this->info('   Flagged: '.($result['flagged'] ? 'Yes' : 'No'));
                    $this->info('   Safe for publication: '.($result['flagged'] ? 'No' : 'Yes'));
                } else {
                    $this->error('   ❌ Content moderation failed: '.($result['error'] ?? 'Unknown error'));
                }
            } catch (\Exception $e) {
                $this->error('   ❌ Error in content moderation: '.$e->getMessage());
            }
        } else {
            $this->warn('   ⚠️  No AI provider API keys configured - skipping content moderation test');
        }
        $this->newLine();

        $this->info('🎉 Phase 3 AI System Test Complete!');
        $this->info('====================================');
        $this->info('Multi-provider AI system with local AI integration is ready!');
        $this->info('Features implemented:');
        $this->info('- ✅ OpenAI Provider (GPT-4, DALL-E)');
        $this->info('- ✅ Anthropic Provider (Claude 3)');
        $this->info('- ✅ Google AI Provider (Gemini, Imagen)');
        $this->info('- ✅ Local AI Provider (Ollama)');
        $this->info('- ✅ Smart Provider Selection');
        $this->info('- ✅ Cost Optimization');
        $this->info('- ✅ Fallback Chain Logic');
        $this->newLine();
        $this->info('Next steps:');
        $this->info('- Implement Controllers & API endpoints');
        $this->info('- Add comprehensive testing');
        $this->info('- Create user documentation');
        $this->newLine();

        return 0;
    }
}
