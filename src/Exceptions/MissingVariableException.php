<?php

namespace MailCarrier\Exceptions;

use MailCarrier\Enums\ApiErrorKey;

class MissingVariableException extends \Exception
{
    public function getErrorKey(): ApiErrorKey
    {
        return ApiErrorKey::MissingVariable;
    }
}
