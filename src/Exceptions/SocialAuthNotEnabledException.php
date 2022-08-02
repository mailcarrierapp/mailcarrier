<?php

namespace MailCarrier\Exceptions;

use MailCarrier\Enums\ApiErrorKey;

class SocialAuthNotEnabledException extends MailCarrierException
{
    public function __construct()
    {
        parent::__construct('Social authentication not enabled.');
    }

    public function getErrorKey(): ApiErrorKey
    {
        return ApiErrorKey::SocialAuthNotEnabled;
    }
}
