<?php

namespace MailCarrier\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    public $signature = 'mailcarrier:install';

    public $description = 'Install MailCarrier.';

    public function handle(): int
    {
        dd(getcwd(), __DIR__);

        $this->deleteDefaultMigrations();
        $this->deleteDefaultModels();
        $this->updateComposerJson();

        $this->call('migrate');

        $this->comment('MailCarrier installed correctly.');

        return self::SUCCESS;
    }

    /**
     * Delete the default Laravel migration.
     */
    protected function deleteDefaultMigrations(): void
    {
        unlink(__DIR__ . '/database/migrations/2014_10_12_000000_create_users_table.php');
        unlink(__DIR__ . '/database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php');
    }

    /**
     * Delete the default Laravel models.
     */
    protected function deleteDefaultModels(): void
    {
        unlink(__DIR__ . '/app/Models/User.php');
    }

    /**
     * Update the composer.json.
     */
    protected function updateComposerJson(): void
    {
        $composerJson = file_get_contents(__DIR__ . '/composer.json');
        $composerJson = str_replace(
            '"@php artisan vendor:publish --tag=laravel-assets --ansi --force"',
            '"@php artisan vendor:publish --tag=laravel-assets --ansi --force",
                            "@php artisan mailcarrier:upgrade"',
            $composerJson
        );

        file_put_contents(__DIR__ . '/composer.json', $composerJson);
    }
}
