<?php

namespace MailCarrier\MailCarrier\Actions;

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\AbstractUser;
use MailCarrier\MailCarrier\Models\User;

class Login extends Action
{
    /**
     * The key holding the user roles array.
     */
    public const SOCIALITE_USER_ROLES_KEY = 'https://theraloss.com/roles';

    /**
     * Create a Socialite user.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException
     */
    public function run(AbstractUser $socialiteUser): User
    {
        $user = User::query()->updateOrCreate(
            [
                'oauth_id' => $socialiteUser->getId(),
            ],
            [
                'name' => $socialiteUser->getName(),
                'email' => $socialiteUser->getEmail(),
                'picture_url' => $socialiteUser->getAvatar(),
                'roles' => $socialiteUser[self::SOCIALITE_USER_ROLES_KEY],
            ]
        );

        Auth::login($user, true);

        return $user;
    }
}
