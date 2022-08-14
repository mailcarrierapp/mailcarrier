<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Socialite\AbstractUser as User;
use MailCarrier\Facades\MailCarrier;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        MailCarrier::authorizeSocialAuth(function (User $user): bool {
            return false;
        });
    }
}
