<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use MailCarrier\Dto\GenericMailDto;
use MailCarrier\Facades\MailCarrier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        MailCarrier::beforeSending(function (GenericMailDto $mail): void {
            return;
        });

        MailCarrier::sending(function (GenericMailDto $mail, \Closure $next): void {
            $next();
        });
    }
}
