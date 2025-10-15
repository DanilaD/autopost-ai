<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This table tracks all posts made through the platform.
     * Supports both immediate posts and scheduled posts.
     * 
     * Post lifecycle:
     * 1. draft - Being created
     * 2. scheduled - Queued for future posting
     * 3. publishing - Currently being sent to Instagram
     * 4. published - Successfully posted
     * 5. failed - Publishing failed
     * 6. cancelled - User cancelled scheduled post
     */
    public function up(): void
    {
        Schema::create('instagram_posts', function (Blueprint $table) {
            $table->id();
            
            // Ownership - which account and which user created this
            $table->foreignId('instagram_account_id')
                ->constrained()
                ->cascadeOnDelete();
            
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            
            // Post content
            $table->text('caption')->nullable();
            $table->string('media_type')->default('image'); // image, video, carousel
            $table->json('media_urls')->nullable(); // Array of media file paths/urls
            
            // Instagram response data
            $table->string('instagram_post_id')->nullable()->unique();
            $table->string('instagram_permalink')->nullable();
            
            // Scheduling
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            
            // Status tracking
            $table->enum('status', [
                'draft',
                'scheduled', 
                'publishing',
                'published',
                'failed',
                'cancelled'
            ])->default('draft');
            
            // Error tracking
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            
            // Metadata and analytics placeholder
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes(); // Allow soft deletion for audit trail
            
            // Indexes for common queries
            $table->index(['instagram_account_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('scheduled_at');
            $table->index('published_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_posts');
    }
};

