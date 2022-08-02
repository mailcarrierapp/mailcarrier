<?php

namespace MailCarrier\Actions\Auth;

use MailCarrier\Actions\Action;

class GenerateToken extends Action
{
    /**
     * Ensure that the Auth Manager user exists.
     */
    public function run(string $name = 'Unnamed'): string
    {
        $authUser = (new EnsureAuthManagerExists)->run();

        return $authUser->createToken($name)->plainTextToken;
    }
}
