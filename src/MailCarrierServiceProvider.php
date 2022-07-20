<?php

namespace MailCarrier\MailCarrier;

use Filament\Events\ServingFilament;
use Filament\Facades\Filament;
use Filament\Navigation\NavigationBuilder;
use Filament\PluginServiceProvider;
use Illuminate\Support\Facades\Event;
use MailCarrier\MailCarrier\Models\Template;
use MailCarrier\MailCarrier\Observers\TemplateObserver;
use MailCarrier\MailCarrier\Resources\LayoutResource;
use MailCarrier\MailCarrier\Resources\LogResource;
use MailCarrier\MailCarrier\Resources\TemplateResource;
use Spatie\LaravelPackageTools\Package;

class MailCarrierServiceProvider extends PluginServiceProvider
{
    public static string $name = 'mailcarrier';

    protected array $scripts = [
        'mailcarrier-scripts' => __DIR__ . '/../dist/js/monaco.js',
    ];

    protected array $resources = [
        LayoutResource::class,
        TemplateResource::class,
        LogResource::class,
    ];

    public function packageConfiguring(Package $package): void
    {
        Event::listen(ServingFilament::class, $this->servingFilament(...));
    }

    public function packageConfigured(Package $package): void
    {
        $package
            ->hasMigrations([
                '1_create_users_table',
                '2_create_layouts_table',
                '3_create_templates_table',
                '4_create_logs_table',
                '5_create_attachments_table',
            ])
            ->runsMigrations();
    }

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

    public function packageBooted(): void
    {
        parent::packageBooted();

        // Observe models
        Template::observe(TemplateObserver::class);

        // Register the "login" gate for Social auth
        $userPolicyClassName = 'App\\Policies\\UserPolicy';

        if (class_exists($userPolicyClassName) && method_exists($userPolicyClassName, 'login')) {
            Gate::define('login', [$userPolicyClassName, 'login']);
        }
    }
}
