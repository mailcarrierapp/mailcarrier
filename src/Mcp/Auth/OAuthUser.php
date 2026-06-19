<?php

namespace MailCarrier\Mcp\Auth;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;

/**
 * Dedicated authenticatable model used by the MCP "api" (Passport) guard.
 *
 * It shares the "users" table with {@see \MailCarrier\Models\User} but uses
 * Passport's token contract, so it stays isolated from the Sanctum-based
 * token functionality that protects the mailing API endpoint.
 */
class OAuthUser extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];
}
