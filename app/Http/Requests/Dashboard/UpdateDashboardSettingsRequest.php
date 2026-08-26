<?php

namespace App\Http\Requests\Dashboard;

use App\Rules\PhoneNumberRule;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateDashboardSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $user = $this->user();
        $currentPhone = $user?->phone;

        // The phone we're about to compare against is the value the request
        // ultimately resolves to — see prepareForValidation() below. If the
        // user hasn't changed it, we don't even apply the unique rule;
        // the format rule still runs to keep stored data sane.
        $requestedPhone = (string) $this->input('phone', '');
        $phoneIsUnchanged = $currentPhone !== null
            && $requestedPhone !== ''
            && hash_equals((string) $currentPhone, $requestedPhone);

        $phoneRules = ['required', 'string', 'max:20', new PhoneNumberRule];

        if (!$phoneIsUnchanged) {
            $phoneRules[] = Rule::unique('users', 'phone')->ignore($user?->id);
        }

        return [
            'name' => 'required|string|max:255',
            'phone' => $phoneRules,
            'country' => 'required|string|max:100',
            'avatar' => 'nullable|file|image|mimes:jpeg,png,jpg,webp|max:3072',
            'remove_avatar' => 'nullable|boolean',
            'current_password' => 'nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.unique' => 'This phone number is already in use by another account.',
        ];
    }

    /**
     * Normalize the submitted phone before validation. Accepts already-E.164
     * input untouched, otherwise tries `dial_code` + `phone` like the
     * registration form. Falls back to leaving the value unchanged (and
     * letting validation reject it).
     */
    protected function prepareForValidation(): void
    {
        $rawPhone = (string) $this->input('phone', '');
        $dialCode = $this->input('dial_code');

        if ($rawPhone === '') {
            return;
        }

        if (PhoneNumber::isValidE164($rawPhone)) {
            // Already in E.164 form (also covers "user did not change it").
            return;
        }

        $normalized = PhoneNumber::normalize($rawPhone, is_string($dialCode) ? $dialCode : null);

        if ($normalized !== null) {
            $this->merge(['phone' => $normalized]);
        }
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->filled('password')) {
                return;
            }
            if (! $this->filled('current_password') || ! Hash::check($this->input('current_password'), $this->user()->password)) {
                $validator->errors()->add('current_password', 'The current password you entered is incorrect.');
            }
        });
    }
}
