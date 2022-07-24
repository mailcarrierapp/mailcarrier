<?php

namespace MailCarrier\Exceptions;

use MailCarrier\Enums\ApiErrorKey;

class TemplateRenderException extends SendingFailedException
{
    public function getErrorKey(): ApiErrorKey
    {
        return ApiErrorKey::TemplateRender;
    }
}
