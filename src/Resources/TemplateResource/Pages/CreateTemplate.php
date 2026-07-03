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
            // Use the custom slug if the user provided one, otherwise generate it from the name
            'slug' => (new GenerateSlug)->run(
                filled($data['slug'] ?? null) ? $data['slug'] : $data['name']
            ),
        ];
    }
}
