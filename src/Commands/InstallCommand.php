<?php

namespace MailCarrier\Commands;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use MailCarrier\Actions\Auth\EnsureAuthManagerExists;
use Symfony\Component\Process\Process;
use function Laravel\Prompts\confirm;

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
        $this->overrideMigrations();
        $this->overrideModels();
        $this->overrideBootstrap();
        $this->overrideRoutes();
        $this->overrideProviders();
        $this->overrideViews();
        $this->overrideReadme();
        $this->deleteFiles();

        $this->labeledLine('Project cleaned up.');
    }

    /**
     * Override the default migrations.
     */
    protected function overrideMigrations(): void
    {
        // Remove existing users table
        $this->call('migrate:rollback');

        @unlink(getcwd() . '/database/migrations/0001_01_01_000000_create_users_table.php');
        @unlink(getcwd() . '/database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php');
    }

    /**
     * Override the default models.
     */
    protected function overrideModels(): void
    {
        @unlink(getcwd() . '/app/Models/User.php');
    }

    /**
     * Override the default models.
     */
    protected function overrideBootstrap(): void
    {
        copy(__DIR__ . '/../../stubs/bootstrap_app.php.stub', getcwd() . '/bootstrap/app.php');
        copy(__DIR__ . '/../../stubs/bootstrap_providers.php.stub', getcwd() . '/bootstrap/providers.php');
    }

    /**
     * Override the default routes.
     */
    protected function overrideRoutes(): void
    {
        @unlink(getcwd() . '/routes/console.php');
        @unlink(getcwd() . '/routes/web.php');
    }

    /**
     * Override the default Service Provider files.
     */
    public function overrideProviders(): void
    {
        copy(__DIR__ . '/../Providers/stubs/AppServiceProvider.php.stub', getcwd() . '/app/Providers/AppServiceProvider.php');
        copy(__DIR__ . '/../Providers/stubs/AuthServiceProvider.php.stub', getcwd() . '/app/Providers/AuthServiceProvider.php');
    }

    /**
     * Override the default views.
     */
    protected function overrideViews(): void
    {
        @unlink(getcwd() . '/resources/views/welcome.blade.php');
    }

    /**
     * Override the default readme.
     */
    protected function overrideReadme(): void
    {
        copy(__DIR__ . '/../../README.md', getcwd() . '/README.md');
    }

    /**
     * Delete not needed files.
     */
    protected function deleteFiles(): void
    {
        @unlink(getcwd() . '/vite.config.js');
        @unlink(getcwd() . '/package.json');
    }

    /**
     * Update the composer.json.
     */
    protected function updateComposerJson(): void
    {
        $composerJsonPath = getcwd() . '/composer.json';
        $composerJson = file_get_contents($composerJsonPath);

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

        $this->callSilently('filament:install', [
            '--no-interaction' => true,
        ]);

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

        $this->callSilently('vendor:publish', [
            '--tag' => 'sanctum-migrations',
        ]);

        $this->labeledLine('Vendor files copied.');
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
        if (confirm('Do you want to setup Social Auth instead of regular one?')) {
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
        if (confirm('Do you want to create a user?', true)) {
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
