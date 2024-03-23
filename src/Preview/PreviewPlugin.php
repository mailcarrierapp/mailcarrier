<?php

namespace MailCarrier\Preview;

use Filament\Panel;
use Livewire\Livewire;
use Pboivin\FilamentPeek\FilamentPeekPlugin;

class PreviewPlugin extends FilamentPeekPlugin
{
    public function register(Panel $panel): void
    {
        parent::register($panel);

        Livewire::component('filament-peek::builder-editor', PreviewBuilderEditor::class);
    }
}
