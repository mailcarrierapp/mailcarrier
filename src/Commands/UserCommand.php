<?php

namespace MailCarrier\Commands;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use MailCarrier\Models\User;
use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

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
            'name' => text('Name', required: true),
            'email' => text(
                'Email address',
                required: true,
                validate: fn (string $value) => $this->validatePrompt($value, ['email', 'unique:\MailCarrier\Models\User,email']),
            ),
            'password' => password(
                'Password',
                hint: 'Leave it blank for a random one',
                validate: fn (?string $value) => $this->validatePrompt($value, ['nullable', 'min:8']),
            ),
        ];

        // Generate a password if not provided
        if (!$data['password']) {
            $data['password'] = $this->rawRandomPassword = Str::password(16);
        }

        // Finally encrypt the password
        $data['password'] = Hash::make($data['password']);

        return $data;
    }
}
