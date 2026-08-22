<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add OAuth/social-login columns + a dedicated identities table.
 *
 * Two-tier design:
 *  - `users.oauth_provider` / `users.oauth_provider_id` capture the user's
 *    PRIMARY social-auth provider and id, kept on the users table for fast
 *    lookup and backward compatibility with existing reads.
 *  - `social_accounts` lets a single user link multiple OAuth providers
 *    (e.g. Google + GitHub) without losing data — so we don't enforce a
 *    one-to-one constraint at the user level.
 *
 * Note: `users.phone` and `users.password` are intentionally left as the
 * existing schema defines them. Socially-onboarded users get a placeholder
 * password (random hash) so the legacy NOT NULL constraint still holds —
 * see App\Services\Auth\SocialAuthService.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('oauth_provider', 32)->nullable()->after('country');
            $table->string('oauth_provider_id', 191)->nullable()->after('oauth_provider');
            $table->string('avatar_url', 512)->nullable()->after('oauth_provider_id');

            $table->index(['oauth_provider', 'oauth_provider_id']);
        });

        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('provider', 32);
            $table->string('provider_id', 191);
            $table->string('email')->nullable();
            $table->string('nickname', 191)->nullable();
            $table->string('name')->nullable();
            $table->string('avatar_url', 512)->nullable();
            $table->text('access_token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_id'], 'social_accounts_provider_unique');
            $table->index(['user_id', 'provider']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_accounts');

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['oauth_provider', 'oauth_provider_id']);
            $table->dropColumn(['oauth_provider', 'oauth_provider_id', 'avatar_url']);
        });
    }
};
