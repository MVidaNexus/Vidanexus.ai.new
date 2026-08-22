<?php

namespace App\Support;

/**
 * Phone number helpers shared between registration, profile updates, and
 * the social-auth onboarding flow.
 */
final class PhoneNumber
{
    /**
     * Normalize free-form phone input to canonical E.164 format.
     *
     * Strategy:
     *  - If a `dialCode` is provided, drop a single leading "0" from the
     *    raw national number (common Egyptian / UAE habit) and concatenate.
     *  - If no dial code is provided but the input already starts with "+",
     *    keep it as-is.
     *  - Otherwise return the original input untouched (let validation
     *    reject it).
     *
     * Always strips spaces, dashes, and parentheses commonly added by users.
     */
    public static function normalize(?string $rawNumber, ?string $dialCode = null): ?string
    {
        $rawNumber = $rawNumber !== null ? trim($rawNumber) : null;

        if ($rawNumber === null || $rawNumber === '') {
            return null;
        }

        // Strip cosmetic separators users tend to add.
        $clean = preg_replace('/[\s\-().]/', '', $rawNumber) ?? $rawNumber;

        if (is_string($dialCode) && $dialCode !== '') {
            $dialCode = trim($dialCode);
            if (!str_starts_with($dialCode, '+')) {
                $dialCode = '+'.preg_replace('/[^0-9]/', '', $dialCode);
            }

            // Drop a single leading "0" from the national number — it's a
            // local trunk prefix, not part of the international number.
            $national = ltrim(preg_replace('/[^0-9]/', '', $clean) ?? '', '0');

            return $dialCode.$national;
        }

        return $clean;
    }

    /**
     * Quick predicate for "is this already a valid-ish E.164 string?"
     */
    public static function isValidE164(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        return (bool) preg_match('/^\+[1-9]\d{7,14}$/', trim($value));
    }
}
