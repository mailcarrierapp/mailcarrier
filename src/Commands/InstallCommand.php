<?php

namespace MailCarrier\Commands;

use Composer\Semver\Comparator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use MailCarrier\Actions\Auth\EnsureAuthManagerExists;
use Symfony\Component\Process\Process;

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

        $this->cleanupLaravel();
        $this->updateComposerJson();
        $this->installFilament();
        $this->publishVendor();
        $this->migrate();
        $this->autoload();
        $this->setupSocialAuth();

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
     * Cleanup the default Laravel project.
     */
    protected function cleanupLaravel(): void
    {
        $this->deleteDefaultMigrations();
        $this->deleteDefaultModels();
        $this->deleteDefaultRoutes();
        $this->deleteDefaultViews();

        $this->labeledLine('Project cleaned up.');
    }

    /**
     * Delete the default Laravel migration.
     */
    protected function deleteDefaultMigrations(): void
    {
        @unlink(getcwd() . '/database/migrations/2014_10_12_000000_create_users_table.php');
        @unlink(getcwd() . '/database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php');
    }

    /**
     * Delete the default Laravel models.
     */
    protected function deleteDefaultModels(): void
    {
        copy(__DIR__ . '/../../src/Models/stubs/User.php.stub', getcwd() . '/app/Models/User.php');
    }

    /**
     * Delete the default Laravel routes.
     */
    protected function deleteDefaultRoutes(): void
    {
        $kernelPath = getcwd() . '/app/Console/Kernel.php';
        $kernel = file_get_contents($kernelPath);

        $kernel = str_replace(
            "require base_path('routes/console.php');
    ",
            '',
            $kernel
        );

        file_put_contents($kernelPath, $kernel);

        @unlink(getcwd() . '/routes/channels.php');
        @unlink(getcwd() . '/routes/console.php');

        copy(__DIR__ . '/../../routes/stubs/api.php.stub', getcwd() . '/routes/api.php');
        copy(__DIR__ . '/../../routes/stubs/web.php.stub', getcwd() . '/routes/web.php');
    }

    /**
     * Delete the default Laravel migration.
     */
    protected function deleteDefaultViews(): void
    {
        @unlink(getcwd() . '/resources/views/welcome.blade.php');
    }

    /**
     * Update the composer.json.
     */
    protected function updateComposerJson(): void
    {
        $composerJsonPath = getcwd() . '/composer.json';
        $composerJson = file_get_contents($composerJsonPath);

        // Set minimum PHP version
        if (Comparator::lessThan($this->getComposerValue($composerJson, 'php'), '^8.1')) {
            $composerJson = $this->setComposerValue($composerJson, 'php', '^8.1');
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
        $composerJson = $this->setComposerValue($composerJson, 'name', 'mailcarrier/app');
        $composerJson = $this->setComposerValue($composerJson, 'description', 'Mailing platform with templates and logs included');

        // Remove keywords
        $currentKeywords = Str::match('/"keywords": (.*),/', $composerJson);
        $composerJson = str_replace(
            '"keywords": ' . $currentKeywords . ',
    ',
            '',
            $composerJson
        );

        file_put_contents($composerJsonPath, $composerJson);

        $this->labeledLine('Composer updated.');
    }

    /**
     * Install Filament and make the necessary edits.
     */
    protected function installFilament(): void
    {
        $this->labeledLine('Installing dashboard...', 'DOING', 'blue-400');

        (new Process(['composer', 'require', 'filament/filament']))
            ->mustRun();

        $this->callSilently('vendor:publish', [
            '--tag' => 'filament-config',
        ]);

        // Replace config values
        $filamentConfigPath = getcwd() . '/config/filament.php';
        $filamentConfig = file_get_contents($filamentConfigPath);

        $filamentConfig = str_replace(
            [
                "'path' => env('FILAMENT_PATH', 'admin')",
                "'brand' => env('APP_NAME')",
                "'dark_mode' => false",
            ],
            [
                "'path' => '/'",
                "'brand' => 'MailCarrier'",
                "'dark_mode' => true",
            ],
            $filamentConfig
        );

        file_put_contents($filamentConfigPath, $filamentConfig);

        $this->labeledLine('Dashboard installed.');
    }

    /**
     * Publish the vendor files.
     */
    protected function publishVendor(): void
    {
        $this->callSilently('vendor:publish', [
            '--tag' => 'mailcarrier-config',
        ]);

        $this->callSilently('vendor:publish', [
            '--tag' => 'mailcarrier-assets',
        ]);

        $this->labeledLine('Configuration files and assets copied.');
    }

    /**
     * Migrate the database.
     */
    protected function migrate(): void
    {
        $this->call('migrate');

        $this->newLine();
        $this->labeledLine('Database migrated.');
    }

    /**
     * Refresh composer autoload to reflect changed files.
     */
    protected function autoload(): void
    {
        $this->labeledLine('Refreshing composer...', 'DOING', 'blue-400');

        (new Process(['composer', 'dump-autoload']))
            ->mustRun();

        $this->labeledLine('Composer fresh and clean.');
    }

    /**
     * Setup Social Auth.
     */
    protected function setupSocialAuth(): void
    {
        if ($this->confirm('Do you want to setup Social Auth instead of regular one?')) {
            $this->call('mailcarrier:social');
        } else {
            (new EnsureAuthManagerExists)->run();

            $this->line('If you change your mind you can still setup it by running:');
            $this->comment('php artisan mailcarrier:social');

            $this->createFirstUser();
        }
    }

    /**
     * Create the first user.
     */
    protected function createFirstUser(): void
    {
        if ($this->confirm('Do you want to create a user?', true)) {
            $this->call('mailcarrier:user');
        } else {
            $this->line('You can create as many users as you want later on by running:');
            $this->comment('php artisan mailcarrier:user');
        }
    }

    /**
     * Get a value from the composer.json file.
     */
    protected function getComposerValue(string $composerJson, string $name): string
    {
        return Str::match('/"' . $name . '": "(.*)"/', $composerJson);
    }

    /**
     * Get a value from the composer.json file.
     */
    protected function setComposerValue(string $composerJson, string $name, string $value): string
    {
        return str_replace(
            sprintf('"%s": "%s"', $name, $this->getComposerValue($composerJson, $name)),
            sprintf('"%s": "%s"', $name, $value),
            $composerJson
        );
    }
}
