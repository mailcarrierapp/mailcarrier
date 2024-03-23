<?php

namespace MailCarrier\Resources\TemplateResource\Actions;

use Pboivin\FilamentPeek\Forms\Actions\InlinePreviewAction;

class LivePreviewAction extends InlinePreviewAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Live preview');
        $this->icon('heroicon-o-bolt');
        $this->button();
        $this->builderPreview('name');
        $this->extraAttributes([
            'class' => '!bg-amber-500 hover:!bg-amber-400 w-full',
        ]);
    }
}
