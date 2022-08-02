<?php

namespace MailCarrier\Helpers;

use Illuminate\Support\Arr;

class SocialiteProviders
{
    /**
     * Get the list of native supported drivers for Socialite.
     */
    public static function getNativeSocialiteProviders(): array
    {
        return [
            'Google',
            'GitHub',
            'GitLab',
            'Bitbucket',
        ];
    }

    /**
     * Get a map of Socialite providers.
     */
    public static function getProvidersMap(): array
    {
        return [
            'Apple',
            'Auth0',
            'Cognito',
            'Keycloak',
            'Microsoft',
            'Okta',
            'Zoho',
        ];
    }

    /**
     * Find a driver by its (lowercase) name.
     */
    public static function findByName(?string $name): ?string
    {
        return Arr::first(
            self::getProvidersMap(),
            fn (string $provider) => strtolower($provider) === $name
        );
    }

    /**
     * Get additional configuration values for a given driver.
     */
    public static function getAdditionalConfig(string $driver): string
    {
        return match ($driver) {
            'Auth0' => <<<'PHP'
            'base_url' => env('AUTH0_BASE_URL'),
            PHP,
            'Okta' => <<<'PHP'
            'base_url' => env('OKTA_BASE_URL'),
            PHP,
            'Cognito' => <<<'PHP'
            'scope' => explode(",", env('COGNITO_LOGIN_SCOPE')),
            'logout_uri' => env('COGNITO_SIGN_OUT_URL'),
            PHP,
            'Keycloak' => <<<'PHP'
            'base_url' => env('KEYCLOAK_BASE_URL'),
            'realms' => env('KEYCLOAK_REALM'),
            PHP,
            default => '',
        };
    }

    /**
     * Get additional environment variables for a given driver.
     */
    public static function getAdditionalEnv(string $driver): string
    {
        return match ($driver) {
            'Auth0' => <<<'ENV'
            AUTH0_BASE_URL=
            ENV,
            'Okta' => <<<'ENV'
            OKTA_BASE_URL=
            ENV,
            'Cognito' => <<<'ENV'
            COGNITO_LOGIN_SCOPE=
            COGNITO_SIGN_OUT_URL=
            ENV,
            'Keycloak' => <<<'ENV'
            KEYCLOAK_BASE_URL=
            KEYCLOAK_REALM=
            ENV,
            default => '',
        };
    }
}
