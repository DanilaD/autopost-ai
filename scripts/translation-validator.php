#!/usr/bin/env php
<?php

/**
 * Translation Validator
 *
 * Validates that all translation keys exist across all languages.
 * Usage: php scripts/translation-validator.php
 */
$languages = ['en', 'es', 'ru'];
$basePath = __DIR__.'/../lang';

$errors = [];
$warnings = [];

echo "\n🌍 Translation Validator\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

// Get all translation files from English (base language)
$enPath = $basePath.'/en';
if (! is_dir($enPath)) {
    echo "❌ English translation directory not found!\n";
    exit(1);
}

$files = glob($enPath.'/*.php');

foreach ($files as $file) {
    $fileName = basename($file);
    echo "📝 Checking: $fileName\n";

    $translations = [];

    // Load translations from all languages
    foreach ($languages as $lang) {
        $langFile = $basePath.'/'.$lang.'/'.$fileName;

        if (! file_exists($langFile)) {
            $errors[] = "Missing file: lang/$lang/$fileName";
            echo "  ❌ [$lang] File not found\n";

            continue;
        }

        $translations[$lang] = include $langFile;

        if (! is_array($translations[$lang])) {
            $errors[] = "Invalid format: lang/$lang/$fileName";
            echo "  ❌ [$lang] Invalid format (not an array)\n";

            continue;
        }

        echo "  ✅ [$lang] Loaded (".count($translations[$lang])." keys)\n";
    }

    // Compare keys across languages
    if (count($translations) === count($languages)) {
        $enKeys = array_keys($translations['en']);

        foreach (['es', 'ru'] as $lang) {
            $langKeys = array_keys($translations[$lang]);

            // Check for missing keys
            $missing = array_diff($enKeys, $langKeys);
            if (! empty($missing)) {
                foreach ($missing as $key) {
                    $errors[] = "Missing key '$key' in lang/$lang/$fileName";
                    echo "  ❌ [$lang] Missing key: '$key'\n";
                }
            }

            // Check for extra keys
            $extra = array_diff($langKeys, $enKeys);
            if (! empty($extra)) {
                foreach ($extra as $key) {
                    $warnings[] = "Extra key '$key' in lang/$lang/$fileName (not in English)";
                    echo "  ⚠️  [$lang] Extra key: '$key'\n";
                }
            }

            // Check for empty translations
            foreach ($langKeys as $key) {
                if (empty($translations[$lang][$key]) && $translations[$lang][$key] !== '0') {
                    $warnings[] = "Empty translation for '$key' in lang/$lang/$fileName";
                    echo "  ⚠️  [$lang] Empty value: '$key'\n";
                }
            }
        }
    }

    echo "\n";
}

// Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
echo "Summary\n";
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";

if (empty($errors) && empty($warnings)) {
    echo "✅ All translations are complete and consistent!\n\n";
    exit(0);
}

if (! empty($errors)) {
    echo '❌ Errors: '.count($errors)."\n";
    foreach ($errors as $error) {
        echo "  • $error\n";
    }
    echo "\n";
}

if (! empty($warnings)) {
    echo '⚠️  Warnings: '.count($warnings)."\n";
    foreach ($warnings as $warning) {
        echo "  • $warning\n";
    }
    echo "\n";
}

if (! empty($errors)) {
    echo "❌ Translation validation failed!\n\n";
    exit(1);
}

echo "⚠️  Translation validation passed with warnings\n\n";
exit(0);
