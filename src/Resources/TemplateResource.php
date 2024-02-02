<?php

namespace MailCarrier\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use MailCarrier\Actions\Templates\GenerateSlug;
use MailCarrier\Forms\Components\CodeEditor;
use MailCarrier\Models\Layout;
use MailCarrier\Models\Template;
use MailCarrier\Models\User;
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
                    ->sortable(),

                Tables\Columns\TextColumn::make('layout')
                    ->label('Layout')
                    ->formatStateUsing(fn (?Layout $state) => $state?->name ?: '-'),

                Tables\Columns\TextColumn::make('user')
                    ->label('User')
                    ->formatStateUsing(fn (?User $state) => $state?->getFilamentName() ?: '-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->label('Duplicate')
                    ->excludeAttributes([
                        'user_id',
                        'is_locked',
                        'slug',
                    ])
                    ->beforeReplicaSaved(function (Template $replica): void {
                        $replica->fill([
                            'user_id' => Auth::id(),
                            'name' => $replica->name . ' (Copy)',
                            'slug' => (new GenerateSlug())->run($replica->name . ' (Copy)'),
                        ]);
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

    /**
     * Get the form content.
     */
    protected static function getFormContent(): Forms\Components\Section
    {
        return Forms\Components\Section::make([
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
                ->helperText(new HtmlString('<span class="text-xs text-slate-500 pl-2">Use this as "template" key in your APIs</span>'))
                ->columnSpanFull()
                ->required(fn (?Template $record) => !is_null($record))
                // Disable field UI if the record exists and user can't unlock it
                ->disabled(fn (?Template $record) => !is_null($record) && $record->is_locked)
                // Save the field if record does not exist or user can unlock it
                ->dehydrated(fn (?Template $record) => is_null($record) || !$record->is_locked)
                ->extraInputAttributes([
                    'onClick' => 'this.select()',
                ]),

            CodeEditor::make('content')
                ->required()
                ->columnSpanFull()
                ->hint(new HtmlString('<a href="https://twig.symfony.com/doc/3.x/templates.html" class="underline text-primary-500 cursor-help" target="_blank" tabindex="-1">Help with syntax</a>'))
                ->hintIcon('heroicon-o-code-bracket-square')
                // Full width
                ->columnSpan(2)
                // Disable field UI if the record exists and user can't unlock it
                ->disabled(fn (?Template $record) => !is_null($record) && $record->is_locked)
                // Save the field if record does not exist or user can unlock it
                ->dehydrated(fn (?Template $record) => is_null($record) || !$record->is_locked),
        ]);
    }

    /**
     * Get the form sidebar.
     */
    protected static function getFormSidebar(): Forms\Components\Section
    {
        return Forms\Components\Section::make([
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

                    $viewLayoutUrl = URL::route('filament.resources.layouts.edit', ['record' => $record->layout_id]);
                    $icon = svg('heroicon-o-external-link', 'w-4 h-4')->toHtml();

                    return new HtmlString(<<<HTML
                        <a class="ml-2 text-primary-500 text-xs underline block" href="{$viewLayoutUrl}" target="_blank">{$icon}</a>
                    HTML);
                }),

            Forms\Components\Placeholder::make('Separator')
                ->label('')
                ->content(new HtmlString('<div class="h-1 border-b border-slate-100 dark:border-slate-700"></div>')),

            Forms\Components\Placeholder::make('Created by')
                ->content(fn (?Template $record) => $record?->user?->getFilamentName() ?: '-'),

            ...Timestamps::make(),
        ]);
    }
}
