<?php

namespace MailCarrier\Commands;

use MailCarrier\Actions\Auth\GenerateToken;

class TokenCommand extends Command
{
    public $signature = 'mailcarrier:token';

    public $description = 'Generate an Auth Token.';

    public function handle(): int
    {
        $name = $this->validateInput(
            fn () => $this->ask('What\'s the name of the token?', 'Unnamed'),
            'name',
            ['required']
        );

        $this->info('This is your brand new token:');
        $this->comment((new GenerateToken())->run($name));

        return self::SUCCESS;
    }
}
