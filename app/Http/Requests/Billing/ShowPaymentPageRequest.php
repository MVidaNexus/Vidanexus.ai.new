<?php

namespace App\Http\Requests\Billing;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ShowPaymentPageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:tool,package',
            'id' => 'required|string|max:255',
            'new_account' => 'nullable|in:0,1',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            redirect('/dashboard')->with('error', 'Invalid payment link.')
        );
    }
}
