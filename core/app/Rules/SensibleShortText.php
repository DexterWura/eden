<?php

namespace App\Rules;

use App\Support\TextSanity;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SensibleShortText implements ValidationRule
{
    public function __construct(
        private int $maxLength = 255
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $message = TextSanity::validateShortText($value, $this->maxLength);
        if ($message !== null) {
            $fail($message);
        }
    }
}
