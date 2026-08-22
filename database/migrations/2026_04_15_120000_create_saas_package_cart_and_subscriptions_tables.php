<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_packages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_yearly', 10, 2)->nullable();
            $table->string('currency', 8)->default('EGP');
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('subscription_package_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_package_id')
                ->constrained('subscription_packages')
                ->cascadeOnDelete();
            $table->string('tool_slug');
            $table->unsignedInteger('credits_per_cycle');
            $table->timestamps();

            $table->unique(['subscription_package_id', 'tool_slug']);
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('open'); // open, locked, converted, abandoned
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_package_id')
                ->constrained('subscription_packages')
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->string('billing_interval', 16)->default('monthly'); // monthly, yearly
            $table->decimal('unit_price_snapshot', 10, 2);
            $table->string('currency_snapshot', 8)->default('EGP');
            $table->string('package_name_snapshot');
            $table->timestamps();

            $table->unique(['cart_id', 'subscription_package_id', 'billing_interval']);
        });

        Schema::create('user_package_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_package_id')
                ->constrained('subscription_packages')
                ->cascadeOnDelete();
            $table->string('status', 32)->default('active'); // trialing, active, canceled, expired, past_due
            $table->string('billing_interval', 16)->default('monthly');
            $table->decimal('unit_price_paid', 10, 2);
            $table->string('currency', 8)->default('EGP');
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->string('external_payment_ref')->nullable();
            $table->foreignId('cart_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['subscription_package_id', 'status']);
        });

        Schema::create('user_package_subscription_tools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_package_subscription_id')
                ->constrained('user_package_subscriptions')
                ->cascadeOnDelete();
            $table->string('tool_slug');
            $table->unsignedInteger('credits_per_cycle');
            $table->timestamps();

            $table->unique(['user_package_subscription_id', 'tool_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_package_subscription_tools');
        Schema::dropIfExists('user_package_subscriptions');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('subscription_package_tools');
        Schema::dropIfExists('subscription_packages');
    }
};
