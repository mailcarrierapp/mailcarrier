<?php

namespace MailCarrier\MailCarrier\Resources\LayoutResource\Pages;

use MailCarrier\MailCarrier\Resources\LayoutResource;
use MailCarrier\MailCarrier\Models\Layout;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\URL;

class ListLayouts extends ListRecords
{
    protected static string $resource = LayoutResource::class;

    /**
     * Fetch the layouts list.
     */
    protected function getTableQuery(): EloquentBuilder
    {
        return Layout::query()
            ->with('user')
            ->withCount('templates');
    }

    /**
     * Get the top-right table actions.
     */
    protected function getActions(): array
    {
        // If no record, just display the button in the table empty state
        if ($this->records->isEmpty()) {
            return [];
        }

        return parent::getActions();
    }

    /**
     * Get table heading when no record is found.
     */
    protected function getTableEmptyStateHeading(): ?string
    {
        return 'No layout found';
    }

    /**
     * Get table description when no record is found.
     */
    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Wanna create your first layout now?';
    }

    /**
     * Get table actions when no record is found.
     */
    protected function getTableEmptyStateActions(): array
    {
        return [
            Action::make('create')
                ->label('Create layout')
                ->url(URL::route('filament.resources.layouts.create'))
                ->icon('heroicon-o-plus')
                ->button(),
        ];
    }
}
