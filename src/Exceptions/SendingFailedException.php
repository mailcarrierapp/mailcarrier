<?php

namespace MailCarrier\Exceptions;

use MailCarrier\Enums\ApiErrorKey;
use MailCarrier\Models\Log;

class SendingFailedException extends \Exception
{
    public ?Log $log = null;

    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public function setLog(Log $log): static
    {
        $this->log = $log;

        return $this;
    }

    public function getErrorKey(): ApiErrorKey
    {
        return ApiErrorKey::SendingFailed;
    }
}
