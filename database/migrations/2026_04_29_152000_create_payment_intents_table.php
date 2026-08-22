<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_intents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('idempotency_key')->unique();
            $table->string('provider', 40)->default('fawaterk');
            $table->string('provider_order_ref')->unique();
            $table->string('payment_type', 20);
            $table->string('payment_target_id', 100);
            $table->integer('amount_egp');
            $table->string('state', 20)->default('pending');
            $table->timestamp('last_event_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['payment_type', 'payment_target_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_intents');
    }
};
