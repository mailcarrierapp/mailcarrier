<?php

namespace MailCarrier\Resources;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use MailCarrier\Actions\Templates\GenerateSlug;
use MailCarrier\Forms\Components\CodeEditor;
use MailCarrier\Models\Template;
use MailCarrier\Resources\TemplateResource\Actions\LivePreviewAction;
use MailCarrier\Resources\TemplateResource\Pages;
use RalphJSmit\Filament\Components\Forms\Timestamps;

class TemplateResource extends Resource
{
    protected static ?string $model = Template::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';

    protected static ?string $navigationGroup = 'Design';

    /**
     * Build the form.
     */
    public static function form(Form $form): Form
    {
        return $form->schema([
            static::getFormContent()->columnSpan(8),
            static::getFormSidebar()->columnSpan(4),
        ])->columns(12);
    }

    /**
     * List all the records.
     */
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(function (Template $record): HtmlString {
                        return new HtmlString(<<<HTML
                            <p>{$record->name}</p>
                            <p class="text-xs text-gray-400">{$record->slug}</p>
                        HTML);
                    }),

                Tables\Columns\TextColumn::make('tags')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters(static::getTableFilters())
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->requiresConfirmation()
                    ->action(function (Template $record) {
                        $name = $record->name . ' (Copy)';

                        $newRecord = $record->replicate();
                        $newRecord->fill([
                            'user_id' => Auth::id(),
                            'name' => $name,
                            'slug' => (new GenerateSlug)->run($name),
                        ])->save();

                        redirect(TemplateResource::getUrl('edit', ['record' => $newRecord]));
                    }),
            ])
            ->bulkActions([])
            ->defaultSort('name')
            ->emptyStateHeading('No template found')
            ->emptyStateDescription('Wanna create your first template now?')
            ->emptyStateActions([
                Tables\Actions\Action::make('create')
                    ->label('Create template')
                    ->url(URL::route('filament.mailcarrier.resources.templates.create'))
                    ->icon('heroicon-o-plus')
                    ->button(),
            ]);
    }

    /**
     * Get Filament CRUD pages.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTemplates::route('/'),
            'create' => Pages\CreateTemplate::route('/create'),
            'edit' => Pages\EditTemplate::route('/{record}/edit'),
        ];
    }

    public static function getFormEditor(): Forms\Components\Component
    {
        return CodeEditor::make('content')
            ->required()
            ->hint(new HtmlString('<a href="https://twig.symfony.com/doc/3.x/templates.html" class="underline text-primary-500 cursor-help" target="_blank" tabindex="-1">Help with syntax</a>'))
            ->hintIcon('heroicon-o-code-bracket-square')
            // Full width
            ->columnSpanFull()
            // Disable field UI if the record exists and user can't unlock it
            ->disabled(fn (?Template $record) => !is_null($record) && $record->is_locked)
            // Save the field if record does not exist or user can unlock it
            ->dehydrated(fn (?Template $record) => is_null($record) || !$record->is_locked);
    }

    /**
     * Get the table filters.
     */
    protected static function getTableFilters(): array
    {
        return [
            Tables\Filters\SelectFilter::make('tags')
                ->searchable()
                ->options(
                    Template::query()
                        ->pluck('tags')
                        ->unique()
                        ->flatten()
                        ->filter()
                        ->mapWithKeys(fn (string $tag) => [$tag => $tag])
                )
                ->query(fn (Builder $query, array $data): Builder => $query->when(
                    !is_null($data['value'] ?? null),
                    fn (Builder $query) => $query->whereJsonContains('tags', $data['value'])
                )),
        ];
    }

    /**
     * Get the form content.
     */
    protected static function getFormContent(): Forms\Components\Grid
    {
        return Grid::make(1)
            ->schema([
                Forms\Components\Section::make([
                    Forms\Components\TextInput::make('name')
                        ->label('Internal name')
                        ->required()
                        ->autofocus()
                        ->columnSpanFull()
                        // Disable field UI if the record exists and user can't unlock it
                        ->disabled(fn (?Template $record) => !is_null($record) && $record->is_locked)
                        // Save the field if record does not exist or user can unlock it
                        ->dehydrated(fn (?Template $record) => is_null($record) || !$record->is_locked),

                    Forms\Components\TextInput::make('slug')
                        ->label('Unique identifier (slug)')
                        ->placeholder('Leave empty to auto generate')
                        ->helperText('Use this as "template" key in your APIs')
                        ->columnSpanFull()
                        ->required(fn (?Template $record) => !is_null($record))
                        // Disable field UI if the record exists and user can't unlock it
                        ->disabled(fn (?Template $record) => !is_null($record) && $record->is_locked)
                        // Save the field if record does not exist or user can unlock it
                        ->dehydrated(fn (?Template $record) => is_null($record) || !$record->is_locked)
                        ->extraInputAttributes([
                            'onClick' => 'this.select()',
                        ]),

                    static::getFormEditor(),
                ]),

                Forms\Components\Section::make([
                    Forms\Components\Textarea::make('description')
                        ->label('Description')
                        ->helperText('A short description of the template, visible only in the admin area')
                        ->placeholder('How is this template being used? What\'s the purpose?')
                        ->columnSpanFull()
                        // Disable field UI if the record exists and user can't unlock it
                        ->disabled(fn (?Template $record) => !is_null($record) && $record->is_locked)
                        // Save the field if record does not exist or user can unlock it
                        ->dehydrated(fn (?Template $record) => is_null($record) || !$record->is_locked),
                ]),
            ]);
    }

    /**
     * Get the form sidebar.
     */
    protected static function getFormSidebar(): Forms\Components\Section
    {
        return Forms\Components\Section::make([
            Forms\Components\Actions::make([
                LivePreviewAction::make(),
            ]),

            Forms\Components\Toggle::make('is_locked')
                ->inline(false)
                // Disable field UI if the record exists and user can't unlock it
                ->disabled(fn (?Template $record) => !is_null($record) && !static::can('unlock', $record))
                // Save the field if record does not exist or user can unlock it
                ->dehydrated(fn (?Template $record) => is_null($record) || static::can('unlock', $record)),

            Forms\Components\Select::make('layoutId')
                ->relationship('layout', 'name')
                ->suffix(function (?Template $record): ?HtmlString {
                    if (!$record?->layout_id) {
                        return null;
                    }

                    $viewLayoutUrl = URL::route('filament.mailcarrier.resources.layouts.edit', [
                        'record' => $record->layout_id,
                    ]);
                    $icon = svg('heroicon-o-arrow-top-right-on-square', 'w-5 h-5')->toHtml();

                    return new HtmlString(<<<HTML
                        <a class="text-primary-500 text-xs block" href="{$viewLayoutUrl}" target="_blank">{$icon}</a>
                    HTML);
                }),

            Forms\Components\TagsInput::make('tags'),

            Forms\Components\Placeholder::make('Separator')
                ->label('')
                ->content(new HtmlString('<div class="h-1 border-b border-slate-100 dark:border-slate-700"></div>')),

            Forms\Components\Placeholder::make('Created by')
                ->content(fn (?Template $record) => $record?->user?->getFilamentName() ?: '-'),

            ...Timestamps::make(),
        ]);
    }
}
