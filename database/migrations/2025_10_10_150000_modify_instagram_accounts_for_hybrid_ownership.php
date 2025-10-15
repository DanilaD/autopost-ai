<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration transforms instagram_accounts from company-only ownership
     * to support hybrid ownership: both user-owned and company-owned accounts.
     * 
     * Key changes:
     * - Make company_id nullable (accounts can be user-owned)
     * - Add user_id for user-owned accounts
     * - Add is_shared flag for sharing user accounts with teams
     * - Add ownership_type for clarity (user/company)
     */
    public function up(): void
    {
        Schema::table('instagram_accounts', function (Blueprint $table) {
            // Make company_id nullable - accounts can now belong to users directly
            $table->foreignId('company_id')->nullable()->change();
            
            // Add user_id for user-owned accounts
            $table->foreignId('user_id')
                ->nullable()
                ->after('company_id')
                ->constrained()
                ->cascadeOnDelete();
            
            // Flag to indicate if this account is shared with others
            $table->boolean('is_shared')->default(false)->after('user_id');
            
            // Explicit ownership type for easier queries
            $table->enum('ownership_type', ['user', 'company'])
                ->default('company')
                ->after('is_shared');
            
            // Add indexes for performance
            $table->index('user_id');
            $table->index(['user_id', 'status']);
            $table->index(['company_id', 'status']);
            $table->index('ownership_type');
        });
        
        // Data migration: mark all existing accounts as company-owned
        DB::table('instagram_accounts')
            ->whereNotNull('company_id')
            ->update(['ownership_type' => 'company']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instagram_accounts', function (Blueprint $table) {
            // Remove indexes
            $table->dropIndex(['instagram_accounts_user_id_index']);
            $table->dropIndex(['instagram_accounts_user_id_status_index']);
            $table->dropIndex(['instagram_accounts_company_id_status_index']);
            $table->dropIndex(['instagram_accounts_ownership_type_index']);
            
            // Remove columns
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'is_shared', 'ownership_type']);
            
            // Make company_id required again
            $table->foreignId('company_id')->nullable(false)->change();
        });
    }
};

