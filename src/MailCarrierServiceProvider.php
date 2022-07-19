<?php

namespace MailCarrier\MailCarrier;

use MailCarrier\MailCarrier\Commands\MailCarrierCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MailCarrierServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('mailcarrier')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_mailcarrier_table')
            ->hasCommand(MailCarrierCommand::class);
    }
}
