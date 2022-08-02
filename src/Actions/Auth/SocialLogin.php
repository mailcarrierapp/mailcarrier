<?php

namespace MailCarrier\Actions\Auth;

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\AbstractUser;
use MailCarrier\Actions\Action;
use MailCarrier\Models\User;

class SocialLogin extends Action
{
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
                'oauth_raw' => $socialiteUser->user,
            ]
        );

        Auth::login($user, true);

        return $user;
    }
}
