<?php

namespace Tests\Unit\Support;

use App\Rules\PhoneNumberRule;
use App\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

class PhoneNumberTest extends TestCase
{
    /**
     * @dataProvider validNumbers
     */
    public function test_valid_e164_passes_rule(string $value): void
    {
        $rule = new PhoneNumberRule();
        $errors = [];
        $rule->validate('phone', $value, function ($message) use (&$errors) {
            $errors[] = $message;
        });
        $this->assertSame([], $errors, "Expected pass for: {$value}");
    }

    /**
     * @dataProvider invalidNumbers
     */
    public function test_invalid_phone_fails_rule(string $value): void
    {
        $rule = new PhoneNumberRule();
        $errors = [];
        $rule->validate('phone', $value, function ($message) use (&$errors) {
            $errors[] = $message;
        });
        $this->assertNotSame([], $errors, "Expected fail for: {$value}");
    }

    public function test_normalize_combines_dial_code_and_strips_leading_zero(): void
    {
        $this->assertSame('+201001234567', PhoneNumber::normalize('01001234567', '+20'));
        $this->assertSame('+971501234567', PhoneNumber::normalize('0501234567', '+971'));
    }

    public function test_normalize_strips_cosmetic_separators(): void
    {
        $this->assertSame('+15551234567', PhoneNumber::normalize('(555) 123-4567', '+1'));
    }

    public function test_normalize_returns_null_for_empty(): void
    {
        $this->assertNull(PhoneNumber::normalize(null));
        $this->assertNull(PhoneNumber::normalize(''));
    }

    public function test_normalize_keeps_existing_e164_format(): void
    {
        $this->assertSame('+201001234567', PhoneNumber::normalize('+201001234567'));
    }

    public function test_is_valid_e164_predicate(): void
    {
        $this->assertTrue(PhoneNumber::isValidE164('+201001234567'));
        $this->assertFalse(PhoneNumber::isValidE164('abc'));
        $this->assertFalse(PhoneNumber::isValidE164(''));
        $this->assertFalse(PhoneNumber::isValidE164(null));
        $this->assertFalse(PhoneNumber::isValidE164('+'));
    }

    public static function validNumbers(): array
    {
        return [
            'Egypt mobile' => ['+201001234567'],
            'UAE mobile' => ['+971501234567'],
            'US number' => ['+15551234567'],
            'short minimum' => ['+12345678'],
        ];
    }

    public static function invalidNumbers(): array
    {
        return [
            'letters' => ['abc123'],
            'too short' => ['123'],
            'plus signs only' => ['+++++'],
            'special chars' => ['!@#$%^&'],
            'no plus prefix' => ['201001234567'],
            'leading zero in country code' => ['+0201001234567'],
            'too long' => ['+1234567890123456789'],
            'empty' => [''],
        ];
    }
}
