<?php

namespace MailCarrier\Resources\TemplateResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use MailCarrier\Models\Template;
use MailCarrier\Resources\TemplateResource;

class ListTemplates extends ListRecords
{
    protected static string $resource = TemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Fetch the templates list.
     */
    protected function getTableQuery(): EloquentBuilder
    {
        return Template::query()
            ->with([
                'user',
                'layout',
            ]);
    }
}
