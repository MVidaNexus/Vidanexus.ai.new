<?php

namespace Tests\Feature\Auth;

use App\Models\SocialAccount;
use App\Models\User;
use App\Services\Auth\SocialAuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.google.client_id' => 'test-client-id',
            'services.google.client_secret' => 'test-client-secret',
            'services.google.redirect' => 'http://localhost/auth/google/callback',
            'services.socialite.enabled' => true,
            'services.socialite.providers' => ['google', 'github', 'microsoft'],
        ]);
    }

    public function test_unsupported_provider_returns_login_with_error(): void
    {
        $response = $this->get('/auth/something/redirect');

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors('email');
    }

    public function test_callback_creates_user_when_email_is_new(): void
    {
        $service = $this->app->make(SocialAuthService::class);
        $remote = $this->makeSocialiteUser('123-google', 'newuser@example.com');

        Socialite::shouldReceive('driver->user')->andReturn($remote);

        $response = $service->handleProviderCallback('google');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);

        $user = User::where('email', 'newuser@example.com')->first();
        $this->assertSame('google', $user->oauth_provider);
        $this->assertSame('123-google', $user->oauth_provider_id);
        $this->assertNotNull($user->email_verified_at);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '123-google',
        ]);
    }

    public function test_callback_links_existing_user_by_email(): void
    {
        $existing = User::factory()->create(['email' => 'shared@example.com']);

        $service = $this->app->make(SocialAuthService::class);
        $remote = $this->makeSocialiteUser('456-google', 'shared@example.com');

        Socialite::shouldReceive('driver->user')->andReturn($remote);

        $service->handleProviderCallback('google');

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $existing->id,
            'provider' => 'google',
            'provider_id' => '456-google',
        ]);
    }

    public function test_callback_signs_in_returning_social_user_without_creating_duplicate(): void
    {
        $user = User::factory()->create([
            'email' => 'returning@example.com',
            'oauth_provider' => 'google',
            'oauth_provider_id' => '789-google',
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => '789-google',
            'email' => 'returning@example.com',
        ]);

        $service = $this->app->make(SocialAuthService::class);
        $remote = $this->makeSocialiteUser('789-google', 'returning@example.com');

        Socialite::shouldReceive('driver->user')->andReturn($remote);

        $service->handleProviderCallback('google');

        $this->assertSame(1, User::where('email', 'returning@example.com')->count());
        $this->assertSame(1, SocialAccount::where('provider_id', '789-google')->count());
    }

    public function test_provider_with_missing_credentials_throws_runtime_exception(): void
    {
        config(['services.google.client_id' => null]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage("Social provider 'google' is not configured");

        $this->app->make(SocialAuthService::class)->redirectToProvider('google');
    }

    private function makeSocialiteUser(string $id, string $email): SocialiteUser
    {
        $mock = Mockery::mock(SocialiteUser::class);
        $mock->shouldReceive('getId')->andReturn($id);
        $mock->shouldReceive('getEmail')->andReturn($email);
        $mock->shouldReceive('getName')->andReturn('Test User');
        $mock->shouldReceive('getNickname')->andReturn(null);
        $mock->shouldReceive('getAvatar')->andReturn(null);
        $mock->token = 'fake-token';
        $mock->refreshToken = 'fake-refresh';
        $mock->expiresIn = 3600;

        return $mock;
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
