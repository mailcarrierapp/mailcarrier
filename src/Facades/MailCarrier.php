<?php

namespace MailCarrier\MailCarrier\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \MailCarrier\MailCarrier\MailCarrier
 */
class MailCarrier extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'mailcarrier';
    }
}
