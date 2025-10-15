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
        Schema::create('post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['image', 'video']);
            $table->string('filename');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->bigInteger('file_size'); // in bytes
            $table->string('storage_path'); // Path in storage
            $table->string('url')->nullable(); // Public URL
            $table->integer('order')->default(0); // For carousel posts
            $table->json('metadata')->nullable(); // Image dimensions, duration, etc.
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['post_id', 'order']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_media');
    }
};
