<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Honeypot\ProtectAgainstSpam;
use Tests\TestCase;

class PhoneValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ProtectAgainstSpam::class);
    }

    public function test_registration_rejects_invalid_phone(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Tester',
            'email' => 'phone-invalid@example.com',
            'dial_code' => '+20',
            'phone' => 'abc',
            'country' => 'Egypt',
            'password' => 'secret-password-1',
            'password_confirmation' => 'secret-password-1',
        ]);

        $response->assertSessionHasErrors(['phone']);
        $this->assertDatabaseMissing('users', ['email' => 'phone-invalid@example.com']);
    }

    public function test_registration_normalizes_and_stores_e164_phone(): void
    {
        $response = $this->post('/register', [
            'name' => 'John Tester',
            'email' => 'phone-valid@example.com',
            'dial_code' => '+20',
            'phone' => '01001234567',
            'country' => 'Egypt',
            'password' => 'secret-password-1',
            'password_confirmation' => 'secret-password-1',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'phone-valid@example.com',
            'phone' => '+201001234567',
        ]);
    }

    public function test_profile_update_passes_when_phone_is_unchanged(): void
    {
        $user = User::factory()->create([
            'phone' => '+201001234567',
            'country' => 'Egypt',
        ]);

        $response = $this->actingAs($user)->post(route('dashboard.settings.update'), [
            'name' => 'John Updated',
            'phone' => '+201001234567',
            'country' => 'Egypt',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('John Updated', $user->fresh()->name);
    }

    public function test_profile_update_rejects_phone_already_taken_by_other_user(): void
    {
        User::factory()->create(['phone' => '+971501234567']);

        $user = User::factory()->create(['phone' => '+201001234567']);

        $response = $this->actingAs($user)->post(route('dashboard.settings.update'), [
            'name' => 'John',
            'phone' => '+971501234567',
            'country' => 'Egypt',
        ]);

        $response->assertSessionHasErrors(['phone']);
    }

    public function test_profile_update_rejects_invalid_phone_format(): void
    {
        $user = User::factory()->create(['phone' => '+201001234567']);

        $response = $this->actingAs($user)->post(route('dashboard.settings.update'), [
            'name' => 'John',
            'phone' => 'abc',
            'country' => 'Egypt',
        ]);

        $response->assertSessionHasErrors(['phone']);
    }
}
