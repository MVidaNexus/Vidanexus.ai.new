<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * E.164-compatible phone number rule.
 *
 * Accepts numbers in international format like:
 *   +201001234567   (Egypt)
 *   +971501234567   (UAE)
 *   +15551234567    (US)
 *
 * Rejects:
 *   abc123, 123, +++++, special characters only.
 *
 * Rules (matches ITU-T E.164 max length 15 digits, min 7 digits including
 * country code):
 *   - Must start with `+`.
 *   - Followed by 1–3 digit country code (no leading zero).
 *   - Followed by national subscriber digits.
 *   - Total digit count is 8–15.
 *
 * Phone numbers without a leading `+` are normalized in
 * {@see PhoneNumber::normalize()} before validation; this rule itself is
 * strict and only accepts the canonical E.164 form.
 */
class PhoneNumberRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || $value === '') {
            $fail('The :attribute must be a valid international phone number (e.g. +201001234567).');
            return;
        }

        $normalized = trim($value);

        if (!preg_match('/^\+[1-9]\d{7,14}$/', $normalized)) {
            $fail('The :attribute must be a valid international phone number in E.164 format (e.g. +201001234567).');
        }
    }
}
