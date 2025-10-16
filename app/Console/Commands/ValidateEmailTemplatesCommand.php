<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ValidateEmailTemplatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:validate-templates {--fix : Attempt to fix common issues}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate email templates for proper translation usage and structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Validating email templates...');

        $emailTemplatesPath = resource_path('views/emails');
        $errors = [];
        $warnings = [];

        if (! File::exists($emailTemplatesPath)) {
            $this->error('❌ Email templates directory not found: '.$emailTemplatesPath);

            return 1;
        }

        // Get all Blade files in the emails directory
        $bladeFiles = File::allFiles($emailTemplatesPath);
        $bladeFiles = array_filter($bladeFiles, function ($file) {
            return $file->getExtension() === 'php';
        });

        if (empty($bladeFiles)) {
            $this->warn('⚠️  No email template files found');

            return 0;
        }

        $this->info('📧 Found '.count($bladeFiles).' email template(s)');

        foreach ($bladeFiles as $file) {
            $this->validateEmailTemplate($file, $errors, $warnings);
        }

        // Display results
        if (! empty($errors)) {
            $this->error('❌ Validation Errors:');
            foreach ($errors as $error) {
                $this->line('  • '.$error);
            }
        }

        if (! empty($warnings)) {
            $this->warn('⚠️  Validation Warnings:');
            foreach ($warnings as $warning) {
                $this->line('  • '.$warning);
            }
        }

        if (empty($errors) && empty($warnings)) {
            $this->info('✅ All email templates are valid!');

            return 0;
        }

        if (! empty($errors)) {
            $this->error('❌ Email template validation failed');

            return 1;
        }

        $this->warn('⚠️  Email template validation completed with warnings');

        return 0;
    }

    /**
     * Validate a single email template file
     */
    private function validateEmailTemplate($file, &$errors, &$warnings)
    {
        $filePath = $file->getPathname();
        $relativePath = str_replace(resource_path('views/'), '', $filePath);
        $content = File::get($filePath);

        $this->line("  📄 Validating: {$relativePath}");

        // Check for hardcoded strings that should be translated
        $this->checkHardcodedStrings($content, $filePath, $errors, $warnings);

        // Check for missing translation keys
        $this->checkTranslationKeys($content, $filePath, $errors, $warnings);

        // Check for proper Blade syntax
        $this->checkBladeSyntax($content, $filePath, $errors);

        // Check for proper email structure
        $this->checkEmailStructure($content, $filePath, $warnings);
    }

    /**
     * Check for hardcoded strings that should be translated
     */
    private function checkHardcodedStrings($content, $filePath, &$errors, &$warnings)
    {
        // Look for hardcoded English text that should be translated
        $hardcodedPatterns = [
            // Common hardcoded phrases
            '/\b(Welcome|Hello|Hi|Dear|Thank you|Best regards|Sincerely|Regards)\b/i',
            // Email-specific phrases
            '/\b(Click here|Visit|Login|Sign up|Subscribe|Unsubscribe)\b/i',
            // Action phrases
            '/\b(Confirm|Verify|Activate|Reset|Update|Change)\b/i',
        ];

        $lines = explode("\n", $content);
        foreach ($lines as $lineNumber => $line) {
            // Skip comments and Blade directives
            if (preg_match('/^\s*{{--|^\s*@|^\s*<!--/', $line)) {
                continue;
            }

            foreach ($hardcodedPatterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    $warnings[] = "{$filePath}:{$lineNumber} - Potential hardcoded text: ".trim($line);
                }
            }
        }
    }

    /**
     * Check for missing translation keys
     */
    private function checkTranslationKeys($content, $filePath, &$errors, &$warnings)
    {
        // Find all translation calls
        preg_match_all('/__\([\'"]([^\'"]+)[\'"]/', $content, $matches);

        foreach ($matches[1] as $key) {
            // Check if translation key exists in language files
            $this->checkTranslationKeyExists($key, $filePath, $errors, $warnings);
        }

        // Check for translation calls with parameters
        preg_match_all('/__\([\'"]([^\'"]+)[\'"],\s*\[([^\]]+)\]\)/', $content, $paramMatches);

        foreach ($paramMatches[1] as $index => $key) {
            $this->checkTranslationKeyExists($key, $filePath, $errors, $warnings);
            $this->checkTranslationParameters($key, $paramMatches[2][$index], $filePath, $warnings);
        }
    }

    /**
     * Check if a translation key exists in language files
     */
    private function checkTranslationKeyExists($key, $filePath, &$errors, &$warnings)
    {
        $locales = ['en', 'ru', 'es'];
        $missingLocales = [];

        foreach ($locales as $locale) {
            $langPath = lang_path("{$locale}/emails.php");

            if (! File::exists($langPath)) {
                $missingLocales[] = $locale;

                continue;
            }

            $langContent = File::get($langPath);
            $langArray = eval('return '.$langContent.';');

            if (! $this->hasNestedKey($langArray, $key)) {
                $missingLocales[] = $locale;
            }
        }

        if (! empty($missingLocales)) {
            $errors[] = "{$filePath} - Missing translation key '{$key}' in locales: ".implode(', ', $missingLocales);
        }
    }

    /**
     * Check translation parameters
     */
    private function checkTranslationParameters($key, $paramsString, $filePath, &$warnings)
    {
        // Parse parameters (simple parsing)
        $params = [];
        if (preg_match_all('/[\'"]([^\'"]+)[\'"]\s*=>\s*[\'"]([^\'"]+)[\'"]/', $paramsString, $matches)) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $params[$matches[1][$i]] = $matches[2][$i];
            }
        }

        // Check if parameters are used in the translation
        foreach ($locales as $locale) {
            $langPath = lang_path("{$locale}/emails.php");
            if (File::exists($langPath)) {
                $langContent = File::get($langPath);
                $langArray = eval('return '.$langContent.';');
                $translation = $this->getNestedValue($langArray, $key);

                if ($translation) {
                    foreach ($params as $paramName => $paramValue) {
                        if (strpos($translation, ":{$paramName}") === false) {
                            $warnings[] = "{$filePath} - Parameter '{$paramName}' not used in translation '{$key}' for locale '{$locale}'";
                        }
                    }
                }
            }
        }
    }

    /**
     * Check Blade syntax
     */
    private function checkBladeSyntax($content, $filePath, &$errors)
    {
        // Check for unclosed Blade directives
        $openDirectives = [];
        $lines = explode("\n", $content);

        foreach ($lines as $lineNumber => $line) {
            // Check for opening directives
            if (preg_match('/@(if|foreach|for|while|section|component)(\s|\(|$)/', $line, $matches)) {
                $openDirectives[] = ['type' => $matches[1], 'line' => $lineNumber + 1];
            }

            // Check for closing directives
            if (preg_match('/@(endif|endforeach|endfor|endwhile|endsection|endcomponent)/', $line, $matches)) {
                $closingType = str_replace('end', '', $matches[1]);
                $found = false;

                for ($i = count($openDirectives) - 1; $i >= 0; $i--) {
                    if ($openDirectives[$i]['type'] === $closingType) {
                        array_splice($openDirectives, $i, 1);
                        $found = true;
                        break;
                    }
                }

                if (! $found) {
                    $errors[] = "{$filePath}:{$lineNumber} - Unmatched closing directive @{$matches[1]}";
                }
            }
        }

        // Check for unclosed directives
        foreach ($openDirectives as $directive) {
            $errors[] = "{$filePath}:{$directive['line']} - Unclosed @{$directive['type']} directive";
        }
    }

    /**
     * Check email structure
     */
    private function checkEmailStructure($content, $filePath, &$warnings)
    {
        // Check for essential email elements
        $checks = [
            'subject' => '/@section\([\'"]subject[\'"]\)/',
            'title' => '/<title>/',
            'unsubscribe' => '/unsubscribe/i',
        ];

        foreach ($checks as $element => $pattern) {
            if (! preg_match($pattern, $content)) {
                $warnings[] = "{$filePath} - Missing {$element} element";
            }
        }

        // Check for proper HTML structure
        if (! preg_match('/<html[^>]*>/', $content)) {
            $warnings[] = "{$filePath} - Missing HTML tag";
        }

        if (! preg_match('/<body[^>]*>/', $content)) {
            $warnings[] = "{$filePath} - Missing body tag";
        }
    }

    /**
     * Check if nested key exists in array
     */
    private function hasNestedKey($array, $key)
    {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $k) {
            if (! is_array($current) || ! array_key_exists($k, $current)) {
                return false;
            }
            $current = $current[$k];
        }

        return true;
    }

    /**
     * Get nested value from array
     */
    private function getNestedValue($array, $key)
    {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $k) {
            if (! is_array($current) || ! array_key_exists($k, $current)) {
                return null;
            }
            $current = $current[$k];
        }

        return $current;
    }
}
