<?php

namespace MailCarrier\Exceptions;

use MailCarrier\Enums\ApiErrorKey;

abstract class MailCarrierException extends \Exception
{
    abstract public function getErrorKey(): ApiErrorKey;
}
