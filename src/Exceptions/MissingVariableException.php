<?php

namespace MailCarrier\MailCarrier\Exceptions;

use MailCarrier\MailCarrier\Enums\ApiErrorKey;

class MissingVariableException extends \Exception
{
    public function getErrorKey(): ApiErrorKey
    {
        return ApiErrorKey::MissingVariable;
    }
}
