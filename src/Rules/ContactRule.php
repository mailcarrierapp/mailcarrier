<?php

namespace MailCarrier\MailCarrier\Rules;

use MailCarrier\MailCarrier\Dto\ContactDto;
use Illuminate\Contracts\Validation\ImplicitRule;
use Illuminate\Contracts\Validation\Rule;

class ContactRule implements Rule, ImplicitRule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        if (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        if (is_array($value) && ContactDto::tryFrom($value)) {
            return true;
        }

        return false;
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'The field must be an email address or an object with "email" and "name" properties.';
    }
}
