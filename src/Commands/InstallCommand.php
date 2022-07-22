<?php

namespace MailCarrier\Commands;

use Composer\Semver\Comparator;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

use function Termwind\render;

class InstallCommand extends Command
{
    public $signature = 'mailcarrier:install';

    public $description = 'Install MailCarrier.';

    public function handle(): int
    {
        if ($error = $this->databaseConnectionFails()) {
            $this->components->error('Database connection refused. ' . $error);

            return self::FAILURE;
        }

        $this->newLine();

        $this->deleteDefaultMigrations();
        $this->deleteDefaultModels();
        $this->updateComposerJson();
        $this->installFilament();
        $this->publishVendor();
        $this->migrate();

        $this->greenAlert('MailCarrier installed correctly. Enjoy!');

        return self::SUCCESS;
    }

    /**
     * Ensure the database connection works.
     */
    protected function databaseConnectionFails(): ?string
    {
        try {
            Schema::hasTable('migrations');
        } catch (QueryException $e) {
            return $e->getMessage();
        }

        return null;
    }

    /**
     * Delete the default Laravel migration.
     */
    protected function deleteDefaultMigrations(): void
    {
        @unlink(getcwd() . '/database/migrations/2014_10_12_000000_create_users_table.php');
        @unlink(getcwd() . '/database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php');

        $this->success('Database migrations cleanup.');
    }

    /**
     * Delete the default Laravel models.
     */
    protected function deleteDefaultModels(): void
    {
        @unlink(getcwd() . '/app/Models/User.php');

        $this->success('Models cleanup.');
    }

    /**
     * Update the composer.json.
     */
    protected function updateComposerJson(): void
    {
        $composerJsonPath = getcwd() . '/composer.json';
        $composerJson = file_get_contents($composerJsonPath);

        // Set minimum PHP version
        $currentMinimumPhp = Str::match('/"php": "(.*)"/', $composerJson);

        if (Comparator::lessThan($currentMinimumPhp, '^8.1')) {
            $composerJson = str_replace(
                sprintf('"php": "%s"', $currentMinimumPhp),
                '"php": "^8.1"',
                $composerJson
            );
        }

        // Install hook to update MailCarrier
        if (!str_contains($composerJson, '"@php artisan mailcarrier:upgrade"')) {
            $composerJson = str_replace(
                '"@php artisan vendor:publish --tag=laravel-assets --ansi --force"',
                '"@php artisan vendor:publish --tag=laravel-assets --ansi --force",
                "@php artisan mailcarrier:upgrade"',
                $composerJson
            );
        }

        // Rename meta data
        $composerJson = str_replace(
            ['laravel/laravel', 'The Laravel Framework'],
            ['mailcarrier/app', 'Mailing platform powered by templates'],
            $composerJson
        );

        $composerJson = str_replace(
            '"keywords": ["framework", "laravel"],
    ',
            '',
            $composerJson
        );

        file_put_contents($composerJsonPath, $composerJson);

        $this->success('Composer dependencies fixed.');
    }

    /**
     * Install Filament and make the necessary edits.
     */
    protected function installFilament(): void
    {
        (new Process(['composer', 'require', 'filament/filament']))
            ->mustRun();

        // Publish filament config
        $this->callSilently('vendor:publish', [
            '--tag' => 'filament-config',
        ]);

        // Replace config values
        $filamentConfigPath = getcwd() . '/config/filament.php';
        $filamentConfig = file_get_contents($filamentConfigPath);

        $filamentConfig = str_replace(
            ["'path' => env('FILAMENT_PATH', 'admin')", "'brand' => env('APP_NAME')"],
            ["'path' => '/'", "'brand' => 'MailCarrier'"],
            $filamentConfig
        );

        file_put_contents($filamentConfigPath, $filamentConfig);

        $this->success('Dashboard configured.');
    }

    /**
     * Publish the vendor files.
     */
    protected function publishVendor(): void
    {
        $this->callSilently('vendor:publish', [
            '--tag' => 'mailcarrier-config',
        ]);

        $this->success('Configuration file copied.');
    }

    /**
     * Migrate the database.
     */
    protected function migrate(): void
    {
        $this->call('migrate');

        $this->success('Database migrated.');
    }

    /**
     * Show a success message for a task.
     */
    protected function success(string $label): void
    {
        render(<<<HTML
            <div class="mx-2 mb-1">
                <span class="px-1 bg-green-400 text-slate-600">DONE</span>
                <span class="ml-1">$label</span>
            </div>
        HTML);
    }

    /**
     * Show a success message for a task.
     */
    protected function greenAlert(string $label): void
    {
        render(<<<HTML
            <div class="w-full mx-2 py-1 mt-1 bg-green-400 text-slate-800 text-center">
                $label
            </div>
        HTML);
    }
}
