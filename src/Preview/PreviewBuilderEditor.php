<?php

namespace MailCarrier\Preview;

use Illuminate\Contracts\View\View as ViewContract;
use Pboivin\FilamentPeek\Livewire\BuilderEditor;

class PreviewBuilderEditor extends BuilderEditor
{
    public function render(): ViewContract
    {
        if ($this->shouldAutoRefresh()) {
            try {
                $this->refreshBuilderPreview();
            } catch (ValidationException $e) {
                // pass
            }
        }

        return view('mailcarrier::livewire.preview.builder-editor');
    }
}
