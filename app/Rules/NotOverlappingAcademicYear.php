<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotOverlappingAcademicYear implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Implement validation logic here
    }
}
