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
        $this->comment('All done');

        return self::SUCCESS;
    }
}
