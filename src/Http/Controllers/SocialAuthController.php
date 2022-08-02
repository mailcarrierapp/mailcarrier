<?php

namespace MailCarrier\Http\Controllers;

use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use MailCarrier\Actions\Auth\SocialLogin;
use MailCarrier\Exceptions\SocialAuthNotEnabledException;
use MailCarrier\Facades\MailCarrier;

class SocialAuthController extends Controller
{
    /**
     * Redirect to OAuth login page.
     */
    public function redirect(): RedirectResponse
    {
        if (!MailCarrier::getSocialAuthDriver()) {
            throw new SocialAuthNotEnabledException();
        }

        return Socialite::driver(MailCarrier::getSocialAuthDriver())->redirect();
    }

    /**
     * Redirect to OAuth login page.
     */
    public function callback(SocialLogin $login): RedirectResponse
    {
        if (!MailCarrier::getSocialAuthDriver()) {
            throw new SocialAuthNotEnabledException();
        }

        /** @var \Laravel\Socialite\AbstractUser $user */
        $user = Socialite::driver(MailCarrier::getSocialAuthDriver())->user();

        // Check if user is allowed to access the platform
        MailCarrier::validateSocialAuth($user);

        $login->run($user);

        return new RedirectResponse(Filament::getUrl());
    }
}
