<?php

namespace MailCarrier\Actions\Auth;

use Carbon\Carbon;
use MailCarrier\Actions\Action;

class GenerateToken extends Action
{
    /**
     * Ensure that the Auth Manager user exists.
     */
    public function run(string $name = 'Unnamed', array $abilities = ['*'], ?string $expiresAt = null): string
    {
        $authUser = (new EnsureAuthManagerExists)->run();

        if ($expiresAt) {
            $expiresAt = Carbon::parse($expiresAt);
        }

        return $authUser->createToken($name, $abilities, $expiresAt)->plainTextToken;
    }
}
