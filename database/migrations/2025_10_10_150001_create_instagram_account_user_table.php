<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This pivot table enables fine-grained sharing of Instagram accounts.
     * Users can share their personal accounts with team members, or company
     * admins can grant specific permissions on company accounts.
     *
     * Permission levels:
     * - can_post: User can create and publish posts to this account
     * - can_manage: User can modify account settings, reconnect, or disconnect
     */
    public function up(): void
    {
        Schema::create('instagram_account_user', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('instagram_account_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Permission flags
            $table->boolean('can_post')->default(true);
            $table->boolean('can_manage')->default(false);

            // Metadata
            $table->timestamp('shared_at')->useCurrent();
            $table->foreignId('shared_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // Ensure unique user-account combinations
            $table->unique(['instagram_account_id', 'user_id'], 'instagram_account_user_unique');

            // Indexes for performance
            $table->index('user_id');
            $table->index(['user_id', 'can_post']);
            $table->index(['instagram_account_id', 'can_manage']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_account_user');
    }
};
