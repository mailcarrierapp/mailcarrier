<?php

namespace MailCarrier\MailCarrier\Resources\TemplateResource\Pages;

use MailCarrier\MailCarrier\Actions\Templates\GenerateSlug;
use MailCarrier\MailCarrier\Resources\TemplateResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateTemplate extends CreateRecord
{
    protected static string $resource = TemplateResource::class;

    /**
     * Mutate data before creating the template.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $data + [
            'user_id' => Auth::id(),
            'slug' => (new GenerateSlug())->run($this->data['name']),
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
