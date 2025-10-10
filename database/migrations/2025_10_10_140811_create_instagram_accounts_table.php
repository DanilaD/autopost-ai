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
        Schema::create('instagram_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('username');
            $table->string('instagram_user_id')->unique();
            $table->text('access_token'); // Encrypted
            $table->timestamp('token_expires_at');
            $table->string('account_type')->default('personal'); // personal, business
            $table->string('profile_picture_url')->nullable();
            $table->integer('followers_count')->default(0);
            $table->string('status')->default('active'); // active, expired, error, disconnected
            $table->timestamp('last_synced_at')->nullable();
            $table->json('metadata')->nullable(); // Additional Instagram profile data
            $table->timestamps();

            $table->index('company_id');
            $table->index('status');
            $table->index('token_expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instagram_accounts');
    }
};
