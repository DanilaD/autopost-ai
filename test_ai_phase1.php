<?php

/**
 * AI System Test Script
 *
 * This script tests the Phase 1 AI implementation to ensure
 * everything is working correctly before proceeding to Phase 2.
 *
 * Run with: php artisan tinker
 * Then copy and paste this code
 */

use App\Models\AiModel;
use App\Services\AI\Services\AIServiceManager;

echo "🤖 Testing AI System - Phase 1 Implementation\n";
echo "==============================================\n\n";

// Test 1: Check AI Models in Database
echo "1. Testing AI Models Database...\n";
$models = AiModel::where('is_active', true)->get();
echo '   ✅ Found '.$models->count()." active AI models\n";
foreach ($models as $model) {
    echo "   - {$model->name} ({$model->provider}) - \${$model->cost_per_token}/token\n";
}
echo "\n";

// Test 2: Initialize AI Service Manager
echo "2. Testing AI Service Manager...\n";
try {
    $aiManager = new AIServiceManager;
    echo "   ✅ AI Service Manager initialized successfully\n";

    // Test provider availability
    $providers = $aiManager->getAvailableProviders();
    echo '   ✅ Available providers: '.implode(', ', array_keys($providers))."\n";

} catch (Exception $e) {
    echo '   ❌ Error initializing AI Service Manager: '.$e->getMessage()."\n";
}
echo "\n";

// Test 3: Test Provider Stats
echo "3. Testing Provider Statistics...\n";
try {
    $stats = $aiManager->getProviderStats();
    foreach ($stats as $provider => $stat) {
        $status = $stat['available'] ? '✅ Available' : '❌ Unavailable';
        echo "   - {$provider}: {$status}\n";
        if ($stat['available']) {
            echo '     Supports Text: '.($stat['supports_text'] ? 'Yes' : 'No')."\n";
            echo '     Supports Image: '.($stat['supports_image'] ? 'Yes' : 'No')."\n";
            echo '     Supports Moderation: '.($stat['supports_moderation'] ? 'Yes' : 'No')."\n";
        }
    }
} catch (Exception $e) {
    echo '   ❌ Error getting provider stats: '.$e->getMessage()."\n";
}
echo "\n";

// Test 4: Test Text Generation (if OpenAI is configured)
echo "4. Testing Text Generation...\n";
if (config('ai.openai.key')) {
    try {
        $result = $aiManager->generateText('Write a short motivational quote about success.');
        if ($result['success']) {
            echo "   ✅ Text generation successful\n";
            echo '   Provider: '.($result['provider'] ?? 'unknown')."\n";
            echo '   Tokens used: '.($result['tokens_used'] ?? 0)."\n";
            echo '   Cost: $'.number_format($result['cost'] ?? 0, 6)."\n";
            echo '   Generated text: '.substr($result['content'] ?? '', 0, 100)."...\n";
        } else {
            echo '   ❌ Text generation failed: '.($result['error'] ?? 'Unknown error')."\n";
        }
    } catch (Exception $e) {
        echo '   ❌ Error in text generation: '.$e->getMessage()."\n";
    }
} else {
    echo "   ⚠️  OpenAI API key not configured - skipping text generation test\n";
}
echo "\n";

// Test 5: Test Caption Generation
echo "5. Testing Caption Generation...\n";
if (config('ai.openai.key')) {
    try {
        $result = $aiManager->generateCaption('A beautiful sunset over the ocean', 'motivational');
        if ($result['success']) {
            echo "   ✅ Caption generation successful\n";
            echo '   Provider: '.($result['provider'] ?? 'unknown')."\n";
            echo '   Style: '.($result['style'] ?? 'unknown')."\n";
            echo '   Generated caption: '.substr($result['content'] ?? '', 0, 150)."...\n";
        } else {
            echo '   ❌ Caption generation failed: '.($result['error'] ?? 'Unknown error')."\n";
        }
    } catch (Exception $e) {
        echo '   ❌ Error in caption generation: '.$e->getMessage()."\n";
    }
} else {
    echo "   ⚠️  OpenAI API key not configured - skipping caption generation test\n";
}
echo "\n";

// Test 6: Test Hashtag Generation
echo "6. Testing Hashtag Generation...\n";
if (config('ai.openai.key')) {
    try {
        $result = $aiManager->generateHashtags('A beautiful sunset over the ocean with peaceful waves', 5);
        if ($result['success']) {
            echo "   ✅ Hashtag generation successful\n";
            echo '   Provider: '.($result['provider'] ?? 'unknown')."\n";
            echo '   Generated hashtags: '.implode(', ', $result['hashtags'] ?? [])."\n";
        } else {
            echo '   ❌ Hashtag generation failed: '.($result['error'] ?? 'Unknown error')."\n";
        }
    } catch (Exception $e) {
        echo '   ❌ Error in hashtag generation: '.$e->getMessage()."\n";
    }
} else {
    echo "   ⚠️  OpenAI API key not configured - skipping hashtag generation test\n";
}
echo "\n";

// Test 7: Test Content Moderation
echo "7. Testing Content Moderation...\n";
if (config('ai.openai.key')) {
    try {
        $result = $aiManager->moderateContent('This is a wonderful day with beautiful weather!');
        if ($result['success']) {
            echo "   ✅ Content moderation successful\n";
            echo '   Provider: '.($result['provider'] ?? 'unknown')."\n";
            echo '   Flagged: '.($result['flagged'] ? 'Yes' : 'No')."\n";
            echo '   Safe for publication: '.($result['flagged'] ? 'No' : 'Yes')."\n";
        } else {
            echo '   ❌ Content moderation failed: '.($result['error'] ?? 'Unknown error')."\n";
        }
    } catch (Exception $e) {
        echo '   ❌ Error in content moderation: '.$e->getMessage()."\n";
    }
} else {
    echo "   ⚠️  OpenAI API key not configured - skipping content moderation test\n";
}
echo "\n";

echo "🎉 Phase 1 AI System Test Complete!\n";
echo "====================================\n";
echo "If all tests passed, the AI system is ready for Phase 2 implementation.\n";
echo "Next steps:\n";
echo "- Implement Anthropic provider\n";
echo "- Implement Google AI provider\n";
echo "- Add fallback chain logic\n";
echo "- Implement cost calculation service\n";
echo "\n";
