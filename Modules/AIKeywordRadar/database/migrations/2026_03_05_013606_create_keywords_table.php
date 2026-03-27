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
        Schema::create('keywords', function (Blueprint $table) {
            $table->id();
            $table->string('keyword');
            $table->string('category')->default('Target');
            $table->string('lang')->default('ar');
            $table->string('source')->nullable();
            $table->unsignedBigInteger('assigned_admin_id')->nullable();
            $table->string('visibility')->default('all'); // all, role, specific
            $table->json('allowed_roles')->nullable();
            $table->json('allowed_admins')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keywords');
    }
};
