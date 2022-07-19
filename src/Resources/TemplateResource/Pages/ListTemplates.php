<?php

namespace MailCarrier\MailCarrier\Resources\TemplateResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\URL;
use MailCarrier\MailCarrier\Models\Template;
use MailCarrier\MailCarrier\Resources\TemplateResource;

class ListTemplates extends ListRecords
{
    protected static string $resource = TemplateResource::class;

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
        return 'No template found';
    }

    /**
     * Get table description when no record is found.
     */
    protected function getTableEmptyStateDescription(): ?string
    {
        return 'Wanna create your first template now?';
    }

    /**
     * Get table actions when no record is found.
     */
    protected function getTableEmptyStateActions(): array
    {
        return [
            Action::make('create')
                ->label('Create template')
                ->url(URL::route('filament.resources.templates.create'))
                ->icon('heroicon-o-plus')
                ->button(),
        ];
    }
}
