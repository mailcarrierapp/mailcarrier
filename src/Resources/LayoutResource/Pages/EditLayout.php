<?php

namespace MailCarrier\Resources\LayoutResource\Pages;

use Filament\Pages\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use MailCarrier\Resources\LayoutResource;

class EditLayout extends EditRecord
{
    protected static string $resource = LayoutResource::class;

    /** @var \MailCarrier\Models\Layout */
    public $record;

    /**
     * Get resource top-right actions.
     */
    protected function getActions(): array
    {
        $canBeDeleted = !$this->record->is_locked &&
            LayoutResource::canDelete($this->record) &&
            !$this->record->templates()->exists();

        return [
            DeleteAction::make()
                ->disabled(!$canBeDeleted),
        ];
    }
}
