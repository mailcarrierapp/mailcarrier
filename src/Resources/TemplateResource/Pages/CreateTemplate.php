<?php

namespace MailCarrier\Resources\TemplateResource\Pages;

use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use MailCarrier\Actions\Templates\GenerateSlug;
use MailCarrier\Resources\TemplateResource;

class CreateTemplate extends CreateRecord
{
    protected static string $resource = TemplateResource::class;

    /**
     * Mutate data before creating the template.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            ...$data,
            'user_id' => Auth::id(),
            'slug' => (new GenerateSlug())->run($this->data['name']),
        ];
    }
}
