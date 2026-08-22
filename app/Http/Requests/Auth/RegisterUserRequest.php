<?php

namespace App\Http\Requests\Auth;

use App\Rules\NotDisposableEmail;
use App\Rules\PhoneNumberRule;
use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email:rfc,dns', 'max:255', 'unique:users', new NotDisposableEmail],
            'dial_code' => 'required|string|regex:/^\+\d{1,4}$/',
            // Raw national number — combined with dial_code into the
            // canonical E.164 number on the way through the service.
            'phone' => 'required|string|regex:/^[0-9]{7,15}$/',
            // The fully-qualified international phone is enforced too: it's
            // unique, and must match E.164 format (centralized rule).
            'phone_e164' => ['required', 'string', 'max:20', new PhoneNumberRule, Rule::unique('users', 'phone')],
            'country' => 'required|string|max:100',
            'password' => 'required|string|min:8|confirmed',
            'selected_plan' => 'nullable|string|in:beginner,starter,growth,pro,ultimate',
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Please provide a valid email address with a real mail server.',
            'dial_code.regex' => 'Invalid country dialing code format.',
            'phone.regex' => 'Phone number must be between 7 and 15 digits.',
            'password.confirmed' => 'The password confirmation does not match.',
            'phone_e164.unique' => 'This phone number is already registered to another account.',
        ];
    }

    /**
     * Normalize the phone into E.164 BEFORE validation. We expose the
     * derived value as `phone_e164` so the rules can validate it and the
     * service can persist it without re-normalizing.
     */
    protected function prepareForValidation(): void
    {
        $dialCode = (string) $this->input('dial_code', '');
        $rawPhone = (string) $this->input('phone', '');

        $this->merge([
            'phone_e164' => PhoneNumber::normalize($rawPhone, $dialCode ?: null),
        ]);
    }
}
