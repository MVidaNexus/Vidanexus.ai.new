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
        if (!Schema::hasTable('user_cms_connections')) {
            Schema::create('user_cms_connections', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('platform', 32)->default('wordpress'); // 'wordpress', 'ghost', 'shopify', 'webhook'
                $table->string('name')->nullable(); // Friendly name e.g. "مدونتي الرئيسية"
                $table->string('site_url', 500); // e.g. "https://example.com"
                $table->string('username')->nullable(); // e.g. "editor"
                $table->text('api_key'); // Encrypted Application Password / API key
                $table->string('default_status', 32)->default('draft'); // 'draft', 'publish', 'pending'
                $table->string('default_category_id')->nullable();
                $table->json('settings')->nullable(); // Extra platform settings (verify_ssl, custom path, etc.)
                $table->timestamp('last_tested_at')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->index(['user_id', 'platform']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cms_connections');
    }
};
