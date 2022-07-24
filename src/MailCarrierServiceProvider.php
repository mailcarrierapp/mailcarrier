<?php

namespace MailCarrier;

use Filament\Events\ServingFilament;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationBuilder;
use Filament\PluginServiceProvider;
use Illuminate\Support\Facades\Event;
use MailCarrier\Commands\InstallCommand;
use MailCarrier\Commands\UpgradeCommand;
use MailCarrier\Models\Template;
use MailCarrier\Observers\TemplateObserver;
use MailCarrier\Resources\LayoutResource;
use MailCarrier\Resources\LogResource;
use MailCarrier\Resources\TemplateResource;
use Spatie\LaravelPackageTools\Package;

class MailCarrierServiceProvider extends PluginServiceProvider
{
    public static string $name = 'mailcarrier';

    protected array $scripts = [
        'mailcarrier' => __DIR__ . '/../dist/js/monaco.js',
    ];

    protected array $resources = [
        LayoutResource::class,
        TemplateResource::class,
        LogResource::class,
    ];

    /**
     * The package is being configured.
     */
    public function packageConfiguring(Package $package): void
    {
        Event::listen(ServingFilament::class, $this->servingFilament(...));
    }

    /**
     * The package has been configured.
     */
    public function packageConfigured(Package $package): void
    {
        $package
            ->hasRoutes(['api', 'web'])
            ->hasCommands([
                InstallCommand::class,
                UpgradeCommand::class,
            ])
            ->hasMigrations([
                '1_create_users_table',
                '2_create_layouts_table',
                '3_create_templates_table',
                '4_create_logs_table',
                '5_create_attachments_table',
            ])
            ->runsMigrations();

        // We use this over standard `->hasAssets()` to publish them inside the public vendor directly
        $this->publishes([
            $this->package->basePath('/../dist') => public_path(),
        ], "{$this->package->shortName()}-assets");
    }

    /**
     * The package has been registered.
     */
    public function packageRegistered(): void
    {
        parent::packageRegistered();

        // Register dependencies
        $this->app->register(\Livewire\LivewireServiceProvider::class);
        $this->app->register(\Filament\FilamentServiceProvider::class);
        $this->app->register(\Laravel\Socialite\SocialiteServiceProvider::class);

        $this->app->scoped('mailcarrier', fn (): MailCarrierManager => new MailCarrierManager());
    }

    /**
     * The package has been booted.
     */
    public function packageBooted(): void
    {
        parent::packageBooted();

        Template::observe(TemplateObserver::class);
    }

    /**
     * Register Filament settings.
     */
    public function servingFilament(): void
    {
        Filament::registerTheme(mix('css/app.css'));

        // Edit the navigation
        Filament::navigation(
            fn (NavigationBuilder $builder): NavigationBuilder => $builder
                ->items(LogResource::getNavigationItems())
                ->group('Design', [
                    ...LayoutResource::getNavigationItems(),
                    ...TemplateResource::getNavigationItems(),
                ])
        );
    }
}
