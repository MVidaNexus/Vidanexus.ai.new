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
        Schema::create('article_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('topic')->nullable();
            $table->string('title')->nullable();
            $table->text('meta_description')->nullable();
            $table->longText('content')->nullable();
            $table->json('seo_data')->nullable(); // LSI keywords, FAQ schema, etc.
            $table->string('status')->default('completed'); // pending, completed, failed
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->string('language', 5)->default('ar');
            $table->integer('word_count')->default(0);
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_histories');
    }
};
