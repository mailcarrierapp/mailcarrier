<?php

namespace MailCarrier\Preview;

use Filament\Panel;
use Livewire\Livewire;
use MailCarrier\Livewire\PreviewTemplate;
use Pboivin\FilamentPeek\FilamentPeekPlugin;

class PreviewPlugin extends FilamentPeekPlugin
{
    public function register(Panel $panel): void
    {
        parent::register($panel);

        Livewire::component('filament-peek::builder-editor', PreviewBuilderEditor::class);
        Livewire::component('mailcarrier::preview-template', PreviewTemplate::class);
    }
}
