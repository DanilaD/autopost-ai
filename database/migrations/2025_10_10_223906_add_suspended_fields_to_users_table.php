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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable()->after('timezone');
            $table->foreignId('suspended_by')->nullable()->constrained('users')->nullOnDelete()->after('suspended_at');
            $table->text('suspension_reason')->nullable()->after('suspended_by');
            $table->timestamp('last_login_at')->nullable()->after('suspension_reason');
            
            $table->index('suspended_at');
            $table->index('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['suspended_by']);
            $table->dropColumn(['suspended_at', 'suspended_by', 'suspension_reason', 'last_login_at']);
        });
    }
};
