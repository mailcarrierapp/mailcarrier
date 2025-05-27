<?php

namespace MailCarrier\Webhooks\Exceptions;

use Exception;

class WebhookValidationException extends Exception
{
    public function __construct()
    {
        parent::__construct('Webhook validation failed.');
    }
}
