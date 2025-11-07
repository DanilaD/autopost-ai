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
        Schema::create('ai_models', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('provider', 50);
            $table->enum('type', ['text', 'image', 'video', 'multimodal']);
            $table->decimal('cost_per_token', 10, 6)->nullable();
            $table->decimal('cost_per_image', 10, 6)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('capabilities')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('provider');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_models');
    }
};
