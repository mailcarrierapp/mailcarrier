<?php

namespace MailCarrier\MailCarrier\Commands;

use Illuminate\Console\Command;

class MailCarrierCommand extends Command
{
    public $signature = 'mailcarrier';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
