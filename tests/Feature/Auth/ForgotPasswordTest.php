<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_link_request_validates_email(): void
    {
        $response = $this->post('/forgot-password', ['email' => 'not-an-email']);

        $response->assertSessionHasErrors('email');
    }

    public function test_reset_link_is_sent_for_existing_user(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertSessionHas('status');
        Notification::assertSentTo($user, \App\Notifications\QueuedResetPassword::class);
    }

    public function test_reset_link_does_not_leak_user_existence(): void
    {
        // For unknown emails, the response message MUST mirror the
        // success message to avoid user enumeration.
        Notification::fake();

        $response = $this->post('/forgot-password', ['email' => 'nobody@example.com']);

        $response->assertSessionHas('status');
        Notification::assertNothingSent();
    }

    public function test_user_can_reset_password_with_valid_token(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password-123'),
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $this->assertTrue(Hash::check('new-password-456', $user->fresh()->password));
    }

    public function test_invalid_token_is_rejected_with_user_friendly_message(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/reset-password', [
            'token' => 'totally-fake-token',
            'email' => $user->email,
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_password_must_be_confirmed(): void
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->post('/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-456',
            'password_confirmation' => 'mismatched',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
