<?php

namespace App\Http\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Class LetterSpaceNumRule
 *
 * @author Abdul Wadood
 */
class LetterSpaceNumRule implements ValidationRule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^[a-zA-Z0-9 .&%-]*$/i', $value)) {
            $fail(trans('validation.letters_spaces_num'));
        }
    }

    /**
     * Get the validation error message.
     * It uses the translation files for the message.
     *
     * @return string
     */
    public function message() {}
}
