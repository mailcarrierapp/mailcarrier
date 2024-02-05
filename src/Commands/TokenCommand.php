<?php

namespace MailCarrier\Commands;

use MailCarrier\Actions\Auth\GenerateToken;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\text;

class TokenCommand extends Command
{
    public $signature = 'mailcarrier:token';

    public $description = 'Generate an Auth Token.';

    public function handle(): int
    {
        $name = text(
            'Token name',
            placeholder: 'Unnamed',
            required: true,
        );

        info('Generated token:');
        note((new GenerateToken())->run($name));

        return self::SUCCESS;
    }
}
