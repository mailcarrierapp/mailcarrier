<?php

namespace MailCarrier\Dto\Validators;

use Attribute;
use MailCarrier\Dto\Contracts\Validator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Email implements Validator
{
    public function validate(mixed $value): ValidationResult
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return ValidationResult::invalid('Value must be a valid email address.');
        }

        return ValidationResult::valid();
    }
}
