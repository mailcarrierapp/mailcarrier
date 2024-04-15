<?php

namespace MailCarrier\Preview;

use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View as ViewContract;
use Pboivin\FilamentPeek\Livewire\BuilderEditor;

class PreviewBuilderEditor extends BuilderEditor
{
    public function render(): ViewContract
    {
        parent::render();

        return view('mailcarrier::preview.builder-editor');
    }

    public function closeBuilderEditor(): void
    {
        parent::closeBuilderEditor();

        Notification::make()
            ->title('Remember to save your changes!')
            ->info()
            ->send();
    }
}
