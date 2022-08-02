<?php

namespace MailCarrier\Exceptions;

use MailCarrier\Enums\ApiErrorKey;

class MissingVariableException extends SendingFailedException
{
    public function getErrorKey(): ApiErrorKey
    {
        return ApiErrorKey::MissingVariable;
    }
}
