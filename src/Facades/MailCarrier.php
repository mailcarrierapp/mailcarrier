<?php

namespace MailCarrier\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Contracts\Filesystem\Filesystem storage(?string $disk = null)
 * @method static string upload(string $content, string $fileName)
 * @method static ?string download(string $resource, ?string $disk = null)
 * @method static int downloadgetFileSize(string $resource, ?string $disk = null)
 * @method static string humanBytes(int $bytes)
 *
 * @see \MailCarrier\MailCarrier\MailCarrierManager
 */
class MailCarrier extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'mailcarrier';
    }
}
