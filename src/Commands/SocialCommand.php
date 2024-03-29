<?php

namespace MailCarrier\Commands;

use Illuminate\Support\Str;
use MailCarrier\Helpers\SocialiteProviders;
use Symfony\Component\Process\Process;
use function Laravel\Prompts\select;

class SocialCommand extends Command
{
    public $signature = 'mailcarrier:social';

    public $description = 'Install Social Authentication.';

    protected const DRIVER_OTHER = 'Other';

    protected string $chosenDriver;

    public function handle(): int
    {
        $this->chosenDriver = select(
            'Select your social auth provider',
            [
                ...SocialiteProviders::getNativeSocialiteProviders(),
                ...SocialiteProviders::getProvidersMap(),
                self::DRIVER_OTHER,
            ],
            scroll: 8,
        );

        $this->installDependency();
        $this->addServicesConfig();
        $this->addEnvs('.env');
        $this->addEnvs('.env.example');

        $this->info('Social Authentication installed correctly.');

        $this->showVariables();

        return self::SUCCESS;
    }

    /**
     * Install additional socialite driver dependency.
     */
    protected function installDependency(): void
    {
        if (!in_array($this->chosenDriver, SocialiteProviders::getProvidersMap())) {
            return;
        }

        $this->labeledLine(
            sprintf('Installing dependencies for <span class="text-blue-400">%s</span>...', $this->chosenDriver),
            'DOING',
            'blue-400'
        );

        $dependency = 'socialiteproviders/' . strtolower($this->chosenDriver);
        (new Process(['composer', 'require', $dependency, '--no-scripts']))
            ->mustRun();

        $this->labeledLine('Dependencies installed.');
    }

    /**
     * Add the settings to services config file.
     */
    protected function addServicesConfig(): void
    {
        if ($this->chosenDriver === self::DRIVER_OTHER) {
            return;
        }

        $configPath = getcwd() . '/config/services.php';
        $config = file_get_contents($configPath);

        $configKey = strtolower($this->chosenDriver);

        // Do not add the entry if already exists
        if (str_contains($config, "'{$configKey}' =>")) {
            return;
        }

        $config = str_replace('];', $this->buildServicesConfig() . PHP_EOL . PHP_EOL . '];', $config);

        file_put_contents($configPath, $config);
    }

    /**
     * Add the needed environment variables.
     */
    protected function addEnvs(string $envFile): void
    {
        // Do not add the entry if already exists
        if (str_contains($envFile, 'MAILCARRIER_SOCIAL_AUTH_DRIVER=')) {
            return;
        }

        $envPath = getcwd() . '/' . $envFile;
        $envFile = file_get_contents($envPath);

        if ($this->chosenDriver === self::DRIVER_OTHER) {
            $envFile .= 'MAILCARRIER_SOCIAL_AUTH_DRIVER=' . PHP_EOL;
        } else {
            $envFile .= 'MAILCARRIER_SOCIAL_AUTH_DRIVER=' . strtolower($this->chosenDriver) .
                PHP_EOL .
                $this->buildEnvs();
        }

        file_put_contents($envPath, $envFile);
    }

    /**
     * Build the services configuration entry.
     */
    protected function buildServicesConfig(): string
    {
        $name = strtolower($this->chosenDriver);
        $envPrefix = strtoupper($this->chosenDriver);
        $driverAdditionalConfig = SocialiteProviders::getAdditionalConfig($this->chosenDriver);

        return <<<PHP
            '{$name}' => [
                'client_id' => env('{$envPrefix}_CLIENT_ID'),
                'client_secret' => env('{$envPrefix}_CLIENT_SECRET'),
                'redirect' => env('{$envPrefix}_REDIRECT_URI'),
                {$driverAdditionalConfig}
            ],
        PHP;
    }

    /**
     * Build the environment variables.
     */
    protected function buildEnvs(): string
    {
        $envPrefix = strtoupper($this->chosenDriver);
        $driverAdditionalEnv = SocialiteProviders::getAdditionalEnv($this->chosenDriver);

        return <<<PHP
        {$envPrefix}_CLIENT_ID=
        {$envPrefix}_CLIENT_SECRET=
        {$envPrefix}_REDIRECT_URI="\${APP_URL}/auth/callback"
        {$driverAdditionalEnv}
        PHP;
    }

    /**
     * Show added environment variables in the console.
     */
    protected function showVariables(): void
    {
        if ($this->chosenDriver === self::DRIVER_OTHER) {
            return;
        }

        $this->info('The following <comment>environment variables</comment> have been added to your .env file and should be reviewed:');
        $this->newLine();

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $this->buildEnvs()) as $line) {
            if (!empty($line)) {
                $this->line('    • ' . Str::before($line, '='));
            }
        }
    }
}
