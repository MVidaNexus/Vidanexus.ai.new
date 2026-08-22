<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_intent_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 40)->default('fawaterk');
            $table->string('provider_event_id', 191)->unique();
            $table->string('provider_order_ref', 191)->nullable()->index();
            $table->string('provider_status', 50);
            $table->boolean('signature_valid')->default(false);
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
