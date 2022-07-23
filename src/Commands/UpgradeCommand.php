<?php

namespace MailCarrier\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class UpgradeCommand extends Command
{
    public $signature = 'mailcarrier:upgrade';

    public $description = 'Upgrade MailCarrier.';

    public function handle(): int
    {
        $this->upgradeFilament();

        return self::SUCCESS;
    }

    /**
     * Upgrade Filament.
     */
    protected function upgradeFilament(): void
    {
        (new Process(['php', 'artisan', 'filament:upgrade']))
            ->mustRun();
    }
}
