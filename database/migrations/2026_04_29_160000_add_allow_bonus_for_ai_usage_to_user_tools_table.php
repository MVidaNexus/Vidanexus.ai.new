<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_tools', function (Blueprint $table) {
            $table->boolean('allow_bonus_for_ai_usage')->default(true);
        });

        // Marketplace-paid unlocks: spend system (wallet) credits only for AI usage; keep row bonus as non-consumable perk display / future use.
        DB::table('user_tools')
            ->where('price_paid', '>', 0)
            ->update(['allow_bonus_for_ai_usage' => false]);
    }

    public function down(): void
    {
        Schema::table('user_tools', function (Blueprint $table) {
            $table->dropColumn('allow_bonus_for_ai_usage');
        });
    }
};
