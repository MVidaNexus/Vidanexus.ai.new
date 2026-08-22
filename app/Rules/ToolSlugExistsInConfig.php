<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ToolSlugExistsInConfig implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $slugs = collect(config('tools.all_tools', []))->pluck('slug')->all();
        if (! in_array($value, $slugs, true)) {
            $fail(__('The selected :attribute is not a registered AI tool.', ['attribute' => $attribute]));
        }
    }
}
