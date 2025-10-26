<?php

namespace App\Http\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Class LetterSpaceRule
 *
 * @author Abdul Wadood
 */
class LetterSpaceRule implements ValidationRule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^[a-zA-Z .&-]*$/i', $value)) {
            $fail(trans('validation.letters_spaces'));
        }
    }
}
