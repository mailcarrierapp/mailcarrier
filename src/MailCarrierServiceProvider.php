<?php

namespace MailCarrier;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Event;
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
            ->hasRoutes(['api', 'web'])
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
        }

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
