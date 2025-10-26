<?php

namespace App\Http\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use PASVL\Validation\Problems\ArrayFailedValidation;
use PASVL\Validation\ValidatorBuilder;
use RuntimeException;

/**
 * It ensures that the array is either empty or contains integer value.
 *
 * @author Abdul Wadood
 */
class IntegerArrayRule implements ValidationRule
{
    private array $pattern = ['*' => ':number :int :positive'];

    /**
     * Determine if the validation rule passes.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $builder = ValidatorBuilder::forArray($this->pattern);
            $builder->build()->validate($value);
        } catch (ArrayFailedValidation|RuntimeException $ex) {
            $fail($ex->getMessage());
        }
    }
}
