<?php

namespace MailCarrier\Dto\Contracts;

use MailCarrier\Dto\Validators\ValidationResult;

interface Validator
{
    public function validate(mixed $value): ValidationResult;
}
