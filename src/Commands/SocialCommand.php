<?php

namespace MailCarrier\Commands;

class SocialCommand extends Command
{
    public $signature = 'mailcarrier:social';

    public $description = 'Install Social Authentication.';

    public function handle(): int
    {
        $this->copyView();

        $this->info('Social Authentication installed correctly.');
        $this->output->writeln(
            '<info>Remember to set the <comment>MAILCARRIER_SOCIAL_AUTH_DRIVER</comment> env variable.</info>',
        );

        return self::SUCCESS;
    }

    /**
     * Publish Social Auth assets.
     */
    protected function copyView(): void
    {
        $targetDir = getcwd() . '/resources/views/vendor/filament';

        @mkdir($targetDir, recursive: true);
        copy(__DIR__ . '/../../resources/views/stubs/login.blade.php.stub', $targetDir . '/login.blade.php');
    }
}
