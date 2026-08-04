<?php

namespace MailCarrier\Exceptions;

use MailCarrier\Enums\ApiErrorKey;

class TemplateNotActiveException extends MailCarrierException
{
    public function getErrorKey(): ApiErrorKey
    {
        return ApiErrorKey::TemplateNotActive;
    }
}
