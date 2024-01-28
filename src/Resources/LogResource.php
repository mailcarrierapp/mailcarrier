<?php

namespace MailCarrier\Resources;

use Filament\Tables\Actions\Action as TablesAction;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use MailCarrier\Actions\Logs\GetTriggers;
use MailCarrier\Dto\LogTemplateDto;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Models\Log;
use MailCarrier\Models\Template;
use MailCarrier\Resources\LogResource\Pages;

class LogResource extends Resource
{
    protected static ?string $model = Log::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    /**
     * List all the records.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recipient')
                    ->searchable()
                    ->tooltip(
                        fn (Tables\Columns\TextColumn $column): ?string => strlen($column->getState()) > $column->getCharacterLimit() ? $column->getState() : null
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (LogStatus $state): string => match ($state) {
                        LogStatus::Pending => 'warning',
                        LogStatus::Failed => 'danger',
                        LogStatus::Sent => 'success',
                    })
                    ->icon(fn (LogStatus $state): string => match ($state) {
                        LogStatus::Pending => 'heroicon-o-clock',
                        LogStatus::Failed => 'heroicon-o-exclamation-triangle',
                        LogStatus::Sent => 'heroicon-o-check-circle',
                    })
                    ->formatStateUsing(fn (LogStatus $state) => ucfirst(mb_strtolower($state->value)))
                    ->tooltip(fn (Log $record) => $record->error),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(
                        fn (Tables\Columns\TextColumn $column): ?string => strlen($column->getState()) > $column->getCharacterLimit() ? $column->getState() : null
                    ),

                Tables\Columns\TextColumn::make('attachments')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->badge()
                    ->separator(',')
                    ->getStateUsing(
                        fn (Log $record): array => $record
                            ->attachments
                            ->pluck('name')
                            ->all()
                    )
                    ->action(
                        Tables\Actions\Action::make('attachments')
                            ->modalContent(fn (Log $record) => View::make('mailcarrier::modals.attachments', [
                                'attachments' => $record->attachments,
                            ]))
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Close')
                            ->modalFooterActionsAlignment(Alignment::Center)
                    ),

                Tables\Columns\TextColumn::make('template_frozen')
                    ->label('Template')
                    ->url(fn (Log $record): ?string =>
                        is_null($record->template_id) ?
                            null :
                            URL::route('filament.mailcarrier.resources.templates.edit', [
                                'record' => $record->template_id,
                            ])
                    )
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn (Log $record): HtmlString =>
                        static::getTemplateValue($record->template_frozen, $record->template)
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent at')
                    ->since()
                    ->tooltip(fn (Log $record): string => $record->created_at->toRfc7231String()),
            ])
            ->recordAction('details')
            ->filters(static::getTableFilters(), layout: FiltersLayout::Modal)
            ->filtersTriggerAction(
                fn (TablesAction $action) => $action
                    ->button()
                    ->label('Filter')
                    ->slideOver(),
            )
            ->actions(
                Tables\Actions\ActionGroup::make(static::getTableActions())
            );
    }

    /**
     * Get Filament CRUD pages.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLogs::route('/'),
        ];
    }

    /**
     * Get the table filters.
     */
    protected static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('trigger')
                ->options((new GetTriggers())->run()),
            Tables\Filters\SelectFilter::make('status')
                ->options(LogStatus::toEntries()),
        ];
    }

    /**
     * Get the table actions.
     */
    protected static function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('details')
                ->icon('heroicon-o-information-circle')
                ->modalContent(fn (Log $record) => View::make('mailcarrier::modals.details', [
                    'log' => $record,
                    'variables' => str_replace( // Fix double quotes inside strings
                        '\"',
                        '\\\"',
                        json_encode($record->variables, JSON_PRETTY_PRINT)
                    ),
                ]))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalFooterActionsAlignment(Alignment::Center),

            Tables\Actions\Action::make('preview')
                ->icon('heroicon-o-eye')
                ->modalContent(fn (Log $record) => View::make('mailcarrier::modals.preview', [
                    'id' => $record->id,
                ]))
                ->modalWidth('7xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->modalFooterActionsAlignment(Alignment::Center),
        ];
    }

    /**
     * Format the template value.
     */
    protected static function getTemplateValue(LogTemplateDto $templateDto, ?Template $template): HtmlString
    {
        $label = $template?->name ?: $templateDto->name;
        $iconColor = is_null($template) ? 'text-red-500' : 'text-yellow-400';
        $icon = match (true) {
            is_null($template) => 'heroicon-o-x-circle',
            $template->getHash() !== $templateDto->hash => 'heroicon-o-exclamation-circle',
            strtolower($template->name) !== strtolower($templateDto->name) => 'heroicon-o-clock',
            default => null,
        };
        $subtitle = match (true) {
            is_null($template) => 'Template has been deleted.',
            $template->getHash() !== $templateDto->hash => 'Template has changed.',
            strtolower($template->name) !== strtolower($templateDto->name) => 'Template has been renamed.',
            default => null,
        };

        return new HtmlString(
            ($icon ? svg($icon, 'w-4 h-4 inline-block mr-1 ' . $iconColor)->toHtml() : '') .
            $label .
            ($subtitle ? '<p class="text-xs mt-1 text-slate-300">' . $subtitle . '</p>' : '')
        );
    }
}
