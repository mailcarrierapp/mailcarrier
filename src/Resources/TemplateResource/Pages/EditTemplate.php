<?php

namespace MailCarrier\Resources\TemplateResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use MailCarrier\Models\Template;
use MailCarrier\Resources\TemplateResource;
use MailCarrier\Resources\TemplateResource\Actions\SendTestAction;

class EditTemplate extends EditRecord
{
    protected static string $resource = TemplateResource::class;

    public function getRecord(): Template
    {
        return $this->record;
    }

    /**
     * Get resource top-right actions.
     */
    protected function getActions(): array
    {
        return [
            SendTestAction::make(),
            Actions\Action::make('save')
                ->label(__('Save changes'))
                ->action('save'),
        ];
    }

    /**
     * Get resource after-form actions.
     */
    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save')
                ->label(__('Save changes'))
                ->action('save'),
            Actions\DeleteAction::make()
                ->disabled($this->getRecord()->is_locked || !TemplateResource::canDelete($this->record)),
        ];
    }
}
