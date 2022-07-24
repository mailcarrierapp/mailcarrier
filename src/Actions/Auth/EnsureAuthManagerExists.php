<?php

namespace MailCarrier\Actions\Auth;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use MailCarrier\Actions\Action;
use MailCarrier\Enums\Auth as AuthEnum;
use MailCarrier\Models\User;

class EnsureAuthManagerExists extends Action
{
    /**
     * Ensure that the Auth Manager user exists.
     */
    public function run(): User
    {
        return User::query()->firstOrCreate([
            'email' => AuthEnum::AuthManagerEmail,
        ], [
            'name' => 'Auth Manager',
            'password' => Hash::make(Str::uuid()->toString()),
        ]);
    }
}
