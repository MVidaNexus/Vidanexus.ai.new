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
        Schema::table('keywords', function (Blueprint $table) {
            // Speed up lookup by user, category and language
            $table->index(['user_id', 'category', 'lang'], 'keywords_user_context_idx');
            
            // Speed up duplication checks and searches
            $table->index('keyword', 'keywords_text_idx');
            
            // Speed up date-based filtering
            $table->index('published_at', 'keywords_pub_date_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('keywords', function (Blueprint $table) {
            $table->dropIndex('keywords_user_context_idx');
            $table->dropIndex('keywords_text_idx');
            $table->dropIndex('keywords_pub_date_idx');
        });
    }
};
