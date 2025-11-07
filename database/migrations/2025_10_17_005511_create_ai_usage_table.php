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
        Schema::create('ai_usage', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('provider', 50);
            $table->string('model', 100);
            $table->enum('type', ['caption', 'image', 'video', 'plan', 'hashtags', 'description', 'chat']);
            $table->unsignedInteger('tokens_used')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0);
            $table->unsignedInteger('requests_count')->default(1);
            $table->date('usage_date');
            $table->timestamps();

            // Indexes for analytics and reporting
            $table->index(['company_id', 'usage_date']);
            $table->index(['user_id', 'usage_date']);
            $table->index(['provider', 'usage_date']);
            $table->index('usage_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_usage');
    }
};
