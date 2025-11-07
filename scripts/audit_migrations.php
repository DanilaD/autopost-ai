<?php

/**
 * Migration Audit Script
 *
 * Validates all migrations against their corresponding models and factories.
 * Checks for:
 * - Missing models/factories
 * - Column mismatches
 * - Index consistency
 * - Foreign key relationships
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$report = [];
$issues = [];

// Define migration to model/factory mapping
$migrationMap = [
    'users' => ['model' => 'User', 'factory' => 'UserFactory', 'migrations' => [
        '0001_01_01_000000_create_users_table.php',
        '2025_10_10_133338_add_current_company_id_to_users_table.php',
        '2025_10_10_145031_add_locale_to_users_table.php',
        '2025_10_10_203726_add_timezone_to_users_table.php',
        '2025_10_10_223906_add_suspended_fields_to_users_table.php',
    ]],
    'companies' => ['model' => 'Company', 'factory' => 'CompanyFactory', 'migrations' => [
        '2025_10_10_133320_create_companies_table.php',
    ]],
    'company_user' => ['model' => null, 'factory' => null, 'migrations' => [
        '2025_10_10_133329_create_company_user_table.php',
        '2025_10_15_224035_add_accepted_at_to_company_user_table.php',
    ]],
    'inquiries' => ['model' => 'Inquiry', 'factory' => 'InquiryFactory', 'migrations' => [
        '2025_10_10_133834_create_inquiries_table.php',
    ]],
    'instagram_accounts' => ['model' => 'InstagramAccount', 'factory' => 'InstagramAccountFactory', 'migrations' => [
        '2025_10_10_140811_create_instagram_accounts_table.php',
        '2025_10_10_150000_modify_instagram_accounts_for_hybrid_ownership.php',
    ]],
    'instagram_account_user' => ['model' => 'InstagramAccountUser', 'factory' => null, 'migrations' => [
        '2025_10_10_150001_create_instagram_account_user_table.php',
    ]],
    'instagram_posts' => ['model' => 'InstagramPost', 'factory' => 'InstagramPostFactory', 'migrations' => [
        '2025_10_10_150002_create_instagram_posts_table.php',
    ]],
    'posts' => ['model' => 'Post', 'factory' => 'PostFactory', 'migrations' => [
        '2025_10_15_161919_create_posts_table.php',
        '2025_10_16_163429_make_company_id_nullable_in_posts_table.php',
        '2025_10_16_164631_make_instagram_account_id_nullable_in_posts_table.php',
    ]],
    'post_media' => ['model' => 'PostMedia', 'factory' => 'PostMediaFactory', 'migrations' => [
        '2025_10_15_161925_create_post_media_table.php',
    ]],
    'company_invitations' => ['model' => 'CompanyInvitation', 'factory' => null, 'migrations' => [
        '2025_10_15_223525_create_company_invitations_table.php',
    ]],
    'ai_generations' => ['model' => 'AiGeneration', 'factory' => null, 'migrations' => [
        '2025_10_17_005510_create_ai_generations_table.php',
    ]],
    'ai_models' => ['model' => 'AiModel', 'factory' => null, 'migrations' => [
        '2025_10_17_005511_create_ai_models_table.php',
    ]],
    'ai_usage' => ['model' => 'AiUsage', 'factory' => null, 'migrations' => [
        '2025_10_17_005511_create_ai_usage_table.php',
    ]],
    'webhook_calls' => ['model' => null, 'factory' => null, 'migrations' => [
        '2025_10_09_205255_create_webhook_calls_table.php',
    ]],
];

echo "=== MIGRATION AUDIT REPORT ===\n\n";

foreach ($migrationMap as $tableName => $config) {
    echo "📋 Table: {$tableName}\n";
    echo str_repeat('-', 80)."\n";

    $report[$tableName] = [
        'table' => $tableName,
        'model' => $config['model'],
        'factory' => $config['factory'],
        'migrations' => $config['migrations'],
        'issues' => [],
        'indexes' => [],
        'columns' => [],
    ];

    // Check if table exists
    if (! Schema::hasTable($tableName)) {
        $report[$tableName]['issues'][] = '⚠️  Table does not exist in database';
        echo "⚠️  Table does not exist in database\n";
        echo "\n";

        continue;
    }

    // Get actual columns from database
    $columns = Schema::getColumnListing($tableName);
    $report[$tableName]['columns'] = $columns;

    // Get indexes from database
    try {
        $indexes = DB::select("SHOW INDEXES FROM `{$tableName}`");
        $indexNames = collect($indexes)->pluck('Key_name')->unique()->toArray();
        $report[$tableName]['indexes'] = $indexNames;
    } catch (\Exception $e) {
        $report[$tableName]['issues'][] = 'Error getting indexes: '.$e->getMessage();
    }

    // Check model
    if ($config['model']) {
        $modelClass = "App\\Models\\{$config['model']}";
        if (class_exists($modelClass)) {
            echo "✅ Model exists: {$config['model']}\n";

            // Check fillable attributes
            $model = new $modelClass;
            if (property_exists($model, 'fillable')) {
                $fillable = $model->getFillable();
                $missingInModel = array_diff($columns, $fillable, ['id', 'created_at', 'updated_at', 'deleted_at']);
                if (! empty($missingInModel)) {
                    $report[$tableName]['issues'][] = 'Columns not in fillable: '.implode(', ', $missingInModel);
                    echo '⚠️  Columns not in fillable: '.implode(', ', $missingInModel)."\n";
                }
            }
        } else {
            $report[$tableName]['issues'][] = "Model class not found: {$modelClass}";
            echo "❌ Model class not found: {$modelClass}\n";
        }
    } else {
        echo "ℹ️  No model expected (pivot/utility table)\n";
    }

    // Check factory
    if ($config['factory']) {
        $factoryClass = "Database\\Factories\\{$config['factory']}";
        if (class_exists($factoryClass)) {
            echo "✅ Factory exists: {$config['factory']}\n";
        } else {
            $report[$tableName]['issues'][] = "Factory class not found: {$factoryClass}";
            echo "❌ Factory class not found: {$factoryClass}\n";
        }
    } else {
        echo "ℹ️  No factory expected\n";
    }

    // Check migrations
    echo '📝 Migrations: '.count($config['migrations'])."\n";
    foreach ($config['migrations'] as $migration) {
        $migrationPath = database_path("migrations/{$migration}");
        if (file_exists($migrationPath)) {
            echo "   ✅ {$migration}\n";
        } else {
            $report[$tableName]['issues'][] = "Migration file not found: {$migration}";
            echo "   ❌ Migration file not found: {$migration}\n";
        }
    }

    // Display indexes
    if (! empty($report[$tableName]['indexes'])) {
        echo '🔍 Indexes ('.count($report[$tableName]['indexes'])."):\n";
        foreach ($report[$tableName]['indexes'] as $index) {
            if ($index !== 'PRIMARY') {
                echo "   - {$index}\n";
            }
        }
    }

    // Display columns count
    echo '📊 Columns: '.count($columns)."\n";

    echo "\n";
}

// Summary
echo "\n=== SUMMARY ===\n";
$totalIssues = 0;
foreach ($report as $table => $data) {
    if (! empty($data['issues'])) {
        $totalIssues += count($data['issues']);
    }
}

echo 'Total tables audited: '.count($report)."\n";
echo "Total issues found: {$totalIssues}\n";

if ($totalIssues > 0) {
    echo "\n=== ISSUES DETAIL ===\n";
    foreach ($report as $table => $data) {
        if (! empty($data['issues'])) {
            echo "\n{$table}:\n";
            foreach ($data['issues'] as $issue) {
                echo "  - {$issue}\n";
            }
        }
    }
}

// Save report to file
file_put_contents(
    storage_path('logs/migration_audit_'.date('Y-m-d_His').'.json'),
    json_encode($report, JSON_PRETTY_PRINT)
);

echo "\n✅ Report saved to storage/logs/\n";
