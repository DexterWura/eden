<?php

namespace App\Rules;

use App\Support\TextSanity;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SensibleCommentBody implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $message = TextSanity::validateCommentBody(is_string($value) ? $value : null);
        if ($message !== null) {
            $fail($message);
        }
    }
}
