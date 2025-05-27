<?php

namespace MailCarrier\Webhooks\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class WebhookValidationException extends UnprocessableEntityHttpException
{
    public function __construct()
    {
        parent::__construct('Webhook validation failed.');
    }
}
