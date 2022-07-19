<?php

namespace MailCarrier\MailCarrier\Resources;

use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Filament\Tables\Actions\Modal\Actions\Action as TablesModalAction;
use Illuminate\Contracts\View\View as ContractView;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use MailCarrier\MailCarrier\Actions\Logs\GetTriggers;
use MailCarrier\MailCarrier\Dto\LogTemplateDto;
use MailCarrier\MailCarrier\Enums\LogStatus;
use MailCarrier\MailCarrier\Models\Log;
use MailCarrier\MailCarrier\Models\Template;
use MailCarrier\MailCarrier\Resources\LogResource\Pages;

class LogResource extends Resource
{
    protected static ?string $model = Log::class;

    protected static ?string $navigationIcon = 'heroicon-o-mail';

    /**
     * List all the records.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('recipient')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn (Tables\Columns\TextColumn $column): ?string => strlen($column->getState()) > $column->getLimit() ? $column->getState() : null
                    ),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => fn (string $state): bool => $state === LogStatus::Pending->value,
                        'danger' => fn (string $state): bool => $state === LogStatus::Failed->value,
                        'success' => fn (string $state): bool => $state === LogStatus::Sent->value,
                    ])
                    ->tooltip(fn (Log $record) => $record->error),

                Tables\Columns\TextColumn::make('subject')
                    ->searchable()
                    ->limit(25)
                    ->tooltip(fn (Tables\Columns\TextColumn $column): ?string => strlen($column->getState()) > $column->getLimit() ? $column->getState() : null
                    ),

                Tables\Columns\TextColumn::make('trigger'),

                Tables\Columns\TagsColumn::make('attachments')
                    ->limit(2)
                    ->getStateUsing(fn (Log $record): array => $record
                            ->attachments
                            ->pluck('name')
                            ->all()
                    )
                    ->extraAttributes(fn (Log $record): array => [
                        'wire:click' => $record->attachments->isNotEmpty() ? 'mountTableAction("attachments", "'.$record->getKey().'")' : '',
                        'class' => $record->attachments->isNotEmpty() ? 'cursor-pointer' : '',
                    ]),

                Tables\Columns\TextColumn::make('template_frozen')
                    ->label('Template')
                    ->url(fn (Log $record): ?string => is_null($record->template_id) ? null : URL::route('filament.resources.templates.edit', [
                        'record' => $record->template_id,
                    ])
                    )
                    ->openUrlInNewTab()
                    ->formatStateUsing(fn (Log $record): HtmlString => static::getTemplateValue($record->template_frozen, $record->template)
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Sent at')
                    ->since()
                    ->tooltip(fn (Log $record): string => $record->created_at->toRfc7231String()),
            ])
            ->filters(static::getTableFilters())
            ->actions(static::getTableActions());
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
            Tables\Actions\Action::make('inspect')
                ->action(fn () => null)
                ->modalContent(fn (Log $record): ContractView => View::make('modals.inspect', [
                    'log' => $record,
                    'variables' => str_replace( // Fix double quotes inside strings
                        '\"',
                        '\\\"',
                        json_encode($record->variables, JSON_PRETTY_PRINT)
                    ),
                ]))
                ->modalActions([
                    TablesModalAction::make('close')
                        ->label('Close')
                        ->cancel()
                        ->color('secondary'),
                ]),

            Tables\Actions\Action::make('preview')
                ->action(fn () => null)
                ->modalContent(fn (Log $record): ContractView => View::make('modals.preview', [
                    'id' => $record->id,
                ]))
                ->modalWidth('7xl')
                ->modalActions([
                    TablesModalAction::make('close')
                        ->label('Close')
                        ->cancel()
                        ->color('secondary'),
                ]),

            Tables\Actions\Action::make('attachments')
                ->action(fn () => null)
                ->modalContent(fn (Log $record): ContractView => View::make('modals.attachments', [
                    'attachments' => $record->attachments,
                ]))
                ->modalActions([
                    TablesModalAction::make('close')
                        ->label('Close')
                        ->cancel()
                        ->color('secondary'),
                ])
                ->extraAttributes([
                    'class' => 'hidden',
                ]),
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
            ($icon ? svg($icon, 'w-4 h-4 inline-block mr-1 '.$iconColor)->toHtml() : '').
            $label.
            ($subtitle ? '<p class="text-xs mt-1 text-slate-300">'.$subtitle.'</p>' : '')
        );
    }
}
