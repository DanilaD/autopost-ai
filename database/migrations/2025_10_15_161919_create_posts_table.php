<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('instagram_account_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['feed', 'reel', 'story', 'carousel']);
            $table->string('title')->nullable();
            $table->text('caption')->nullable();
            $table->json('hashtags')->nullable(); // Array of hashtags
            $table->json('mentions')->nullable(); // Array of user mentions
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'publishing', 'published', 'failed'])->default('draft');
            $table->text('failure_reason')->nullable();
            $table->integer('publish_attempts')->default(0);
            $table->json('metadata')->nullable(); // Additional data for Instagram API
            $table->timestamps();

            // Indexes
            $table->index(['company_id', 'status']);
            $table->index(['instagram_account_id', 'status']);
            $table->index(['scheduled_at', 'status']);
            $table->index(['created_by', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
