<?php

namespace MailCarrier\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void authorizeSocialAuth(\Closure $callback)
 * @method static void validateSocialAuth(\Laravel\Socialite\AbstractUser $user)
 * @method static string|null getSocialAuthDriver()
 * @method static \Illuminate\Contracts\Filesystem\Filesystem storage(?string $disk = null)
 * @method static string upload(string $content, string $fileName)
 * @method static string|null download(string $resource, ?string $disk = null)
 * @method static int downloadgetFileSize(string $resource, ?string $disk = null)
 * @method static string humanBytes(int $bytes)
 *
 * @see \MailCarrier\MailCarrierManager
 */
class MailCarrier extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'mailcarrier';
    }
}
