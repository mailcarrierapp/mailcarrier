<?php

namespace MailCarrier\MailCarrier\Http\Controllers;

use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Facades\Socialite;
use MailCarrier\MailCarrier\Actions\Login;

class AuthController extends Controller
{
    /**
     * Redirect to OAuth login page.
     */
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('auth0')->redirect();
    }

    /**
     * Redirect to OAuth login page.
     */
    public function callback(Login $login): RedirectResponse
    {
        /** @var \Laravel\Socialite\AbstractUser $socialiteUser */
        $socialiteUser = Socialite::driver('auth0')->user();

        // Check if user is allowed to access the platform
        Gate::authorize('login', [$socialiteUser]);

        $login->run($socialiteUser);

        return new RedirectResponse(Filament::getUrl());
    }
}
