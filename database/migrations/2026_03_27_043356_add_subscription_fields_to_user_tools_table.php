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
        Schema::table('user_tools', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('bonus_credits');
            $table->timestamp('renews_at')->nullable()->after('expires_at');
            $table->boolean('auto_renew')->default(true)->after('renews_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_tools', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'renews_at', 'auto_renew']);
        });
    }
};
