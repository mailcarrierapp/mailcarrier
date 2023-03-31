<?php

namespace MailCarrier\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use MailCarrier\Dto\ContactDto;

class ContactRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        if (is_array($value) && ContactDto::tryFrom($value)) {
            return;
        }

        $fail('The :attribute must be an email address or an object with "email" and "name" properties.');
    }
}
