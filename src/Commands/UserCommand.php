<?php

namespace MailCarrier\Commands;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use MailCarrier\Models\User;

class UserCommand extends Command
{
    public $signature = 'mailcarrier:user';

    public $description = 'Create a new MailCarrier user.';

    protected ?string $rawRandomPassword = null;

    public function handle(): int
    {
        if (Config::get('mailcarrier.social_auth_driver')) {
            $this->error('Cannot create a user when Social Authentication is enabled.');

            return self::INVALID;
        }

        User::create($this->getUserData());

        $this->info('User created successfully.');

        if ($this->rawRandomPassword) {
            $this->comment('Your super secret password is: ' . $this->rawRandomPassword . '');
        }

        return self::SUCCESS;
    }

    /**
     * Prompt and return the user data.
     */
    protected function getUserData(): array
    {
        $data = [
            'name' => $this->validateInput(fn () => $this->ask('Name'), 'name', ['required']),
            'email' => $this->validateInput(fn () => $this->ask('Email address'), 'email', [
                'required',
                'email',
                'unique:MailCarrier\Models\User',
            ]),
            'password' => $this->validateInput(fn () => $this->secret('Password (leave it blank to generate a random one)'), 'password', [
                'nullable',
                'min:8',
            ]),
        ];

        // Generate a password if not provided
        if (!$data['password']) {
            $data['password'] = $this->rawRandomPassword = md5(Str::random(32) . Str::uuid()->toString());
        }

        // Finally encrypt the password
        $data['password'] = Hash::make($data['password']);

        return $data;
    }
}
