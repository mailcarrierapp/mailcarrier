<?php

namespace MailCarrier\Resources\LayoutResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use MailCarrier\Models\Layout;
use MailCarrier\Resources\LayoutResource;

class ListLayouts extends ListRecords
{
    protected static string $resource = LayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Fetch the layouts list.
     */
    protected function getTableQuery(): EloquentBuilder
    {
        return Layout::query()
            ->with('user')
            ->withCount('templates');
    }
}
