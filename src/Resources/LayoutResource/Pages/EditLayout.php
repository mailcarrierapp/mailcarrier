<?php

namespace MailCarrier\Resources\LayoutResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use MailCarrier\Models\Layout;
use MailCarrier\Resources\LayoutResource;

class EditLayout extends EditRecord
{
    protected static string $resource = LayoutResource::class;

    public function getRecord(): Layout
    {
        return $this->record;
    }

    /**
     * Get resource top-right actions.
     */
    protected function getActions(): array
    {
        return [
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
        $canBeDeleted = !$this->getRecord()->is_locked &&
            LayoutResource::canDelete($this->record) &&
            !$this->record->templates()->exists();

        return [
            Actions\Action::make('save')
                ->label(__('Save changes'))
                ->action('save'),

            Actions\DeleteAction::make()
                ->disabled(!$canBeDeleted),
        ];
    }
}
