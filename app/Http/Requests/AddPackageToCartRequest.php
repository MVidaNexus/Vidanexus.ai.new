<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddPackageToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'subscription_package_id' => [
                'required',
                'integer',
                Rule::exists('subscription_packages', 'id')->where('is_active', true),
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'billing_interval' => ['required', 'string', Rule::in(['monthly', 'yearly'])],
        ];
    }
}
