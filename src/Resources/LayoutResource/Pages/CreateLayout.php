<?php

namespace MailCarrier\MailCarrier\Resources\LayoutResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use MailCarrier\MailCarrier\Resources\LayoutResource;

class CreateLayout extends CreateRecord
{
    protected static string $resource = LayoutResource::class;

    /**
     * Mutate data before creating the layout.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + [
            'user_id' => Auth::id(),
        ];
    }

    /**
     * Get resource top-right actions.
     */
    protected function getActions(): array
    {
        return [
            $this->getSubmitFormAction(),
        ];
    }
}
