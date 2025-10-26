<?php

namespace App\Http\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

/**
 * Class PhoneNumberRule
 * @package App\Http\Rules
 * @author Abdul Wadood
 */
class PhoneNumberRule implements ValidationRule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!(preg_match('/^[0-9]*$/i', $value) && Str::length($value) >= 9 && Str::length($value) <= 14)) {
            $fail(trans('validation.phone_number'));
        }
    }
}
