<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('keywords', 'headline_title')) {
            Schema::table('keywords', function (Blueprint $table) {
                $table->string('headline_title', 500)->nullable()->after('source');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('keywords', 'headline_title')) {
            Schema::table('keywords', function (Blueprint $table) {
                $table->dropColumn('headline_title');
            });
        }
    }
};
