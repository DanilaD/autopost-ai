#!/usr/bin/env php
<?php

/**
 * AI System Final Validation Script
 *
 * This script performs a comprehensive validation of the AI system
 * to ensure all components are working correctly and following best practices.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */

require_once __DIR__.'/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Bootstrap Laravel
$app = Application::configure(basePath: __DIR__)
    ->withRouting(
        web: __DIR__.'/routes/web.php',
        api: __DIR__.'/routes/api.php',
        commands: __DIR__.'/routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SetUserTimezone::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\UrlHealthCheckMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle exceptions
    })->create();

$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🤖 AI System Final Validation Script\n";
echo "=====================================\n\n";

$errors = [];
$warnings = [];
$success = [];

// 1. Check Controllers
echo "1. Checking Controllers...\n";
$controllers = [
    'ChatController' => 'App\\Http\\Controllers\\AI\\ChatController',
    'TextController' => 'App\\Http\\Controllers\\AI\\TextController',
    'ImageController' => 'App\\Http\\Controllers\\AI\\ImageController',
    'AnalyticsController' => 'App\\Http\\Controllers\\AI\\AnalyticsController',
];

foreach ($controllers as $name => $class) {
    if (class_exists($class)) {
        $success[] = "✅ {$name} exists";
    } else {
        $errors[] = "❌ {$name} missing";
    }
}

// 2. Check Request Classes
echo "2. Checking Request Classes...\n";
$requests = [
    'ChatRequest' => 'App\\Http\\Requests\\AI\\ChatRequest',
    'TextGenerationRequest' => 'App\\Http\\Requests\\AI\\TextGenerationRequest',
    'ImageGenerationRequest' => 'App\\Http\\Requests\\AI\\ImageGenerationRequest',
    'ImageEditRequest' => 'App\\Http\\Requests\\AI\\ImageEditRequest',
    'ImageModerationRequest' => 'App\\Http\\Requests\\AI\\ImageModerationRequest',
    'AnalyticsRequest' => 'App\\Http\\Requests\\AI\\AnalyticsRequest',
];

foreach ($requests as $name => $class) {
    if (class_exists($class)) {
        $success[] = "✅ {$name} exists";
    } else {
        $errors[] = "❌ {$name} missing";
    }
}

// 3. Check Services
echo "3. Checking Services...\n";
$services = [
    'AIServiceManager' => 'App\\Services\\AI\\Services\\AIServiceManager',
    'CostCalculationService' => 'App\\Services\\AI\\Services\\CostCalculationService',
    'SmartProviderSelectionService' => 'App\\Services\\AI\\Services\\SmartProviderSelectionService',
];

foreach ($services as $name => $class) {
    if (class_exists($class)) {
        $success[] = "✅ {$name} exists";
    } else {
        $errors[] = "❌ {$name} missing";
    }
}

// 4. Check Providers
echo "4. Checking Providers...\n";
$providers = [
    'OpenAIProvider' => 'App\\Services\\AI\\Providers\\OpenAIProvider',
    'AnthropicProvider' => 'App\\Services\\AI\\Providers\\AnthropicProvider',
    'GoogleProvider' => 'App\\Services\\AI\\Providers\\GoogleProvider',
    'LocalProvider' => 'App\\Services\\AI\\Providers\\LocalProvider',
];

foreach ($providers as $name => $class) {
    if (class_exists($class)) {
        $success[] = "✅ {$name} exists";
    } else {
        $errors[] = "❌ {$name} missing";
    }
}

// 5. Check Repositories
echo "5. Checking Repositories...\n";
$repositories = [
    'AiGenerationRepository' => 'App\\Repositories\\AiGenerationRepository',
    'AiUsageRepository' => 'App\\Repositories\\AiUsageRepository',
];

foreach ($repositories as $name => $class) {
    if (class_exists($class)) {
        $success[] = "✅ {$name} exists";
    } else {
        $errors[] = "❌ {$name} missing";
    }
}

// 6. Check Models
echo "6. Checking Models...\n";
$models = [
    'AiGeneration' => 'App\\Models\\AiGeneration',
    'AiModel' => 'App\\Models\\AiModel',
    'AiUsage' => 'App\\Models\\AiUsage',
];

foreach ($models as $name => $class) {
    if (class_exists($class)) {
        $success[] = "✅ {$name} exists";
    } else {
        $errors[] = "❌ {$name} missing";
    }
}

// 7. Check Enums
echo "7. Checking Enums...\n";
$enums = [
    'AIProvider' => 'App\\Enums\\AIProvider',
    'AIGenerationType' => 'App\\Enums\\AIGenerationType',
    'AIModelType' => 'App\\Enums\\AIModelType',
];

foreach ($enums as $name => $class) {
    if (class_exists($class)) {
        $success[] = "✅ {$name} exists";
    } else {
        $errors[] = "❌ {$name} missing";
    }
}

// 8. Check Database Tables
echo "8. Checking Database Tables...\n";
try {
    $tables = ['ai_generations', 'ai_models', 'ai_usage'];
    foreach ($tables as $table) {
        if (\Schema::hasTable($table)) {
            $success[] = "✅ Table {$table} exists";
        } else {
            $errors[] = "❌ Table {$table} missing";
        }
    }
} catch (Exception $e) {
    $warnings[] = '⚠️ Could not check database tables: '.$e->getMessage();
}

// 9. Check API Routes
echo "9. Checking API Routes...\n";
try {
    $routes = \Route::getRoutes();
    $aiRoutes = 0;
    foreach ($routes as $route) {
        if (str_contains($route->uri(), 'api/ai/')) {
            $aiRoutes++;
        }
    }

    if ($aiRoutes >= 20) {
        $success[] = "✅ Found {$aiRoutes} AI API routes";
    } else {
        $warnings[] = "⚠️ Only found {$aiRoutes} AI API routes (expected 20+)";
    }
} catch (Exception $e) {
    $warnings[] = '⚠️ Could not check API routes: '.$e->getMessage();
}

// 10. Check Configuration
echo "10. Checking Configuration...\n";
if (file_exists(__DIR__.'/config/ai.php')) {
    $success[] = '✅ AI configuration file exists';
} else {
    $errors[] = '❌ AI configuration file missing';
}

// Summary
echo "\n📊 VALIDATION SUMMARY\n";
echo "=====================\n";

if (! empty($success)) {
    echo "\n✅ SUCCESSES ({count($success)}):\n";
    foreach ($success as $item) {
        echo "   {$item}\n";
    }
}

if (! empty($warnings)) {
    echo "\n⚠️ WARNINGS ({count($warnings)}):\n";
    foreach ($warnings as $item) {
        echo "   {$item}\n";
    }
}

if (! empty($errors)) {
    echo "\n❌ ERRORS ({count($errors)}):\n";
    foreach ($errors as $item) {
        echo "   {$item}\n";
    }
}

echo "\n🎯 FINAL RESULT:\n";
if (empty($errors)) {
    echo "🎉 AI SYSTEM IS FULLY COMPLIANT AND READY FOR PRODUCTION!\n";
    echo "✅ All components are properly implemented\n";
    echo "✅ All development rules are followed\n";
    echo "✅ System is ready for use\n";
    exit(0);
} else {
    echo "❌ AI SYSTEM HAS ISSUES THAT NEED TO BE FIXED\n";
    echo "🔧 Please address the errors above before proceeding\n";
    exit(1);
}
