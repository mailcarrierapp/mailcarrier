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
        $this->deleteDefaultRoutes();
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
     * Delete the default Laravel migration.
     */
    protected function deleteDefaultMigrations(): void
    {
        @unlink(getcwd() . '/database/migrations/2014_10_12_000000_create_users_table.php');
        @unlink(getcwd() . '/database/migrations/2019_12_14_000001_create_personal_access_tokens_table.php');

        $this->labeledLine('Database migrations cleanup.');
    }

    /**
     * Delete the default Laravel models.
     */
    protected function deleteDefaultModels(): void
    {
        @unlink(getcwd() . '/app/Models/User.php');

        $this->labeledLine('Models cleanup.');
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

        copy(__DIR__ . '/../../routes/api.php.stub', getcwd() . '/routes/api.php');
        copy(__DIR__ . '/../../routes/web.php.stub', getcwd() . '/routes/web.php');

        $this->labeledLine('Routes cleanup.');
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

        $this->labeledLine('Dashboard configured.');
    }

    /**
     * Publish the vendor files.
     */
    protected function publishVendor(): void
    {
        $this->callSilently('vendor:publish', [
            '--tag' => 'mailcarrier-config',
        ]);

        $this->labeledLine('Configuration file copied.');
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
        (new Process(['composer', 'dump-autoload']))
            ->mustRun();
    }

    /**
     * Setup Social Auth.
     */
    protected function setupSocialAuth(): void
    {
        if ($this->confirm('Do you want to setup Social Auth instead of regular one?')) {
            $this->call('mailcarrier:social');
        } else {
            $this->line('If you change your mind later you still setup it by running:');
            $this->comment('php artisan mailcarrier:social');
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

    /**
     * Show a success message for a task.
     */
    protected function labeledLine(string $line, string $label = 'DONE', string $bgColor = 'green-400', string $textColor = 'slate-600'): void
    {
        render(<<<HTML
            <div class="mx-2 mb-1">
                <span class="px-1 bg-$bgColor text-$textColor">$label</span>
                <span class="ml-1">$line</span>
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
