<?php

namespace App\Rules;

use App\Support\TextSanity;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SensiblePersonName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $message = TextSanity::validatePersonName($value);
        if ($message !== null) {
            $fail($message);
        }
    }
}
