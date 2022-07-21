<?php

namespace MailCarrier\Commands;

use Illuminate\Console\Command;

use function Termwind\render;

class InstallCommand extends Command
{
    public $signature = 'mailcarrier:install';

    public $description = 'Install MailCarrier.';

    public function handle(): int
    {
        $this->taskSucceeded('Test.');
        dd(getcwd(), __DIR__);

        $this->deleteDefaultMigrations();
        $this->deleteDefaultModels();
        $this->updateComposerJson();
        $this->publishVendor();
        $this->migrate();

        $this->info('MailCarrier installed correctly. Enjoy!');

        return self::SUCCESS;
    }

    /**
     * Delete the default Laravel migration.
     */
    protected function deleteDefaultMigrations(): void
    {
        unlink(__DIR__ . '/database/migrations/2014_10_12_000000_create_users_table.php');
        unlink(__DIR__ . '/database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php');

        $this->taskSucceeded('Database migrations cleanup.');
    }

    /**
     * Delete the default Laravel models.
     */
    protected function deleteDefaultModels(): void
    {
        unlink(__DIR__ . '/app/Models/User.php');

        $this->taskSucceeded('Models cleanup.');
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

        $this->taskSucceeded('Composer hooks installed.');
    }

    /**
     * Publish the vendor files.
     */
    protected function publishVendor(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'mailcarrier-config',
        ]);

        $this->taskSucceeded('Configuration file copied.');
    }

    /**
     * Migrate the database.
     */
    protected function migrate(): void
    {
        $this->call('migrate');

        $this->taskSucceeded('Database migrated.');
    }

    /**
     * Show a success message for a task.
     */
    protected function taskSucceeded(string $label): void
    {
        render(<<<HTML
            <div class="flex items-center">
                <div class="px-4 py-1.5 bg-green-400 text-slate-700 mr-2">DONE</div>
                <div class="text-slate-50">$label</div>
            </div>
        HTML);
    }
}
