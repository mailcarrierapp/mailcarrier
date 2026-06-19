<?php

namespace MailCarrier;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Laravel\Passport\Passport;
use MailCarrier\Commands\InstallCommand;
use MailCarrier\Commands\RetryCommand;
use MailCarrier\Commands\SocialCommand;
use MailCarrier\Commands\TokenCommand;
use MailCarrier\Commands\UpgradeCommand;
use MailCarrier\Commands\UserCommand;
use MailCarrier\Facades\MailCarrier;
use MailCarrier\Helpers\SocialiteProviders;
use MailCarrier\Models\Layout;
use MailCarrier\Models\Log;
use MailCarrier\Models\Template;
use MailCarrier\Observers\LayoutObserver;
use MailCarrier\Observers\LogObserver;
use MailCarrier\Observers\TemplateObserver;
use MailCarrier\Providers\Filament\MailCarrierPanelProvider;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class MailCarrierServiceProvider extends PackageServiceProvider
{
    public static string $name = 'mailcarrier';

    /**
     * The package has been configured.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('mailcarrier')
            ->hasRoutes(['api', 'web', 'ai'])
            ->hasConfigFile()
            ->hasViews()
            ->hasAssets()
            ->hasCommands([
                InstallCommand::class,
                UpgradeCommand::class,
                SocialCommand::class,
                UserCommand::class,
                TokenCommand::class,
                RetryCommand::class,
            ])
            ->discoversMigrations()
            ->runsMigrations();
    }

    /**
     * The package has been registered.
     */
    public function packageRegistered(): void
    {
        // Register dependencies
        $this->app->register(MailCarrierPanelProvider::class);

        if ($this->app->runningInConsole()) {
            $this->app->register(\Livewire\LivewireServiceProvider::class);
            $this->app->register(\Filament\FilamentServiceProvider::class);
            $this->app->register(\Laravel\Socialite\SocialiteServiceProvider::class);
            $this->app->register(\Laravel\Passport\PassportServiceProvider::class);
            $this->app->register(\Laravel\Mcp\Server\McpServiceProvider::class);
        }

        $this->registerMcpAuth();

        $this->app->scoped('mailcarrier', fn (): MailCarrierManager => new MailCarrierManager);
    }

    /**
     * The package has been booted.
     */
    public function packageBooted(): void
    {
        FilamentAsset::register([
            Js::make('mailcarrier', asset('vendor/mailcarrier/js/mailcarrier.js')),
        ]);

        Template::observe(TemplateObserver::class);
        Layout::observe(LayoutObserver::class);
        Log::observe(LogObserver::class);

        // Register Social Auth event listener
        $this->listenSocialiteEvents();

        // Wire the OAuth consent screen used by the MCP server
        $this->bootMcpAuth();
    }

    /**
     * Register the auth guard and provider used by the MCP server.
     *
     * The MCP server is protected by OAuth 2.1 (Passport) through a dedicated
     * "api" guard backed by the {@see \MailCarrier\Mcp\Auth\OAuthUser} model.
     * This keeps it isolated from the Sanctum-based token functionality that
     * protects the mailing API endpoint.
     */
    protected function registerMcpAuth(): void
    {
        if (!Config::boolean('mailcarrier.mcp.enabled')) {
            return;
        }

        Config::set('auth.guards.api', Config::array('auth.guards.api', [
            'driver' => 'passport',
            'provider' => 'mcp_users',
        ]));

        Config::set('auth.providers.mcp_users', Config::array('auth.providers.mcp_users', [
            'driver' => 'eloquent',
            'model' => \MailCarrier\Mcp\Auth\OAuthUser::class,
        ]));
    }

    /**
     * Boot the OAuth consent screen used by the MCP server.
     */
    protected function bootMcpAuth(): void
    {
        if (!Config::boolean('mailcarrier.mcp.enabled') || !class_exists(Passport::class)) {
            return;
        }

        Passport::authorizationView(
            View::exists('mcp.authorize') ? 'mcp.authorize' : 'mcp::authorize'
        );
    }

    /**
     * Listen to Socialite events for custom (supported) drivers.
     */
    protected function listenSocialiteEvents(): void
    {
        if (!$socialiteName = SocialiteProviders::findByName(MailCarrier::getSocialAuthDriver())) {
            return;
        }

        $listenerClass = sprintf(
            '\SocialiteProviders\%s\%sExtendSocialite',
            $socialiteName,
            $socialiteName
        );

        Event::listen(
            \SocialiteProviders\Manager\SocialiteWasCalled::class,
            [$listenerClass, 'handle']
        );
    }
}
