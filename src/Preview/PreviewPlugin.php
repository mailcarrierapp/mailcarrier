<?php

namespace MailCarrier\Preview;

use Filament\Panel;
use Filament\Support\Assets;
use Filament\Support\Facades\FilamentAsset;
use Livewire\Livewire;
use MailCarrier\Livewire\PreviewTemplate;
use Pboivin\FilamentPeek\FilamentPeekPlugin;

class PreviewPlugin extends FilamentPeekPlugin
{
    public function register(Panel $panel): void
    {
        Livewire::component('filament-peek::builder-editor', PreviewBuilderEditor::class);
        Livewire::component('mailcarrier::preview-template', PreviewTemplate::class);

        $panel->renderHook(
            'panels::body.end',
            fn () => view('mailcarrier::preview.preview-modal'),
        );

        if ($this->shouldLoadPluginScripts()) {
            FilamentAsset::register([
                Assets\Js::make(static::ID, __DIR__ . '/../resources/dist/filament-peek.js'),
            ], package: static::PACKAGE);
        }

        if ($this->shouldLoadPluginStyles()) {
            FilamentAsset::register([
                Assets\Css::make(static::ID, __DIR__ . '/../resources/dist/filament-peek.css'),
            ], package: static::PACKAGE);
        }
    }
}
