<?php

namespace MailCarrier\MailCarrier\Tests;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Config;
use MailCarrier\MailCarrier\MailCarrierServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(fn (string $modelName): string => 'MailCarrier\\MailCarrier\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app): array
    {
        return [
            MailCarrierServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        Config::set('database.default', 'testing');

        /*
        $migration = include __DIR__.'/../database/migrations/create_mailcarrier_table.php.stub';
        $migration->up();
        */
    }
}
