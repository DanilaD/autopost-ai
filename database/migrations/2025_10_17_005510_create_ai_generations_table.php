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
        Schema::create('ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['caption', 'image', 'video', 'plan', 'hashtags', 'description', 'chat'])->index();
            $table->string('provider', 50)->index();
            $table->string('model', 100);
            $table->text('prompt');
            $table->longText('result')->nullable();
            $table->unsignedInteger('tokens_used')->default(0);
            $table->unsignedInteger('cost_credits')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['company_id', 'type']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_generations');
    }
};
