<?php

namespace MailCarrier\MailCarrier;

use Filament\Facades\Filament;
use Filament\Navigation\NavigationBuilder;
use Filament\PluginServiceProvider;
use MailCarrier\MailCarrier\Models\Template;
use MailCarrier\MailCarrier\Observers\TemplateObserver;
use MailCarrier\MailCarrier\Resources\LayoutResource;
use MailCarrier\MailCarrier\Resources\LogResource;
use MailCarrier\MailCarrier\Resources\TemplateResource;

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

    public function packageBooted(): void
    {
        parent::packageBooted();

        // Register the theme
        Filament::serving(function () {
            Filament::registerTheme(mix('css/app.css'));
        });

        // Edit the navigation
        Filament::navigation(
            fn (NavigationBuilder $builder): NavigationBuilder => $builder
                ->items(LogResource::getNavigationItems())
                ->group('Design', [
                    ...LayoutResource::getNavigationItems(),
                    ...TemplateResource::getNavigationItems(),
                ])
        );

        // Observe models
        Template::observe(TemplateObserver::class);

        // Register the "login" gate for Social auth
        $userPolicyClassName = 'App\\Policies\\UserPolicy';

        if (class_exists($userPolicyClassName) && method_exists($userPolicyClassName, 'login')) {
            Gate::define('login', [$userPolicyClassName, 'login']);
        }
    }
}
