<?php

namespace MailCarrier\Resources;

use Filament\Actions\Action as FilamentAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\HtmlString;
use MailCarrier\Models\Layout;
use MailCarrier\Models\User;
use MailCarrier\Resources\LayoutResource\Pages;
use MailCarrier\Schemas\Components\Divider;
use RalphJSmit\Filament\Components\Forms\Timestamps;

class LayoutResource extends Resource
{
    protected static ?string $model = Layout::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-plus';

    protected static string|\UnitEnum|null $navigationGroup = 'Design';

    /**
     * Default HTML content.
     */
    protected const DEFAULT_CONTENT = <<<'HTML'
        <!doctype html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
            </head>
            <body>
                {% block content %}{% endblock %}
            </body>
        </html>
        HTML;

    /**
     * Build the form.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                static::getFormContent()->columnSpan(8),
                static::getFormSidebar()->columnSpan(4),
            ])
            ->columns(12);
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

                Tables\Columns\TextColumn::make('templates_count'),

                Tables\Columns\TextColumn::make('user')
                    ->label('User')
                    ->formatStateUsing(fn (?User $state) => $state?->getFilamentName() ?: '-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->defaultSort('name')
            ->emptyStateHeading('No layout found')
            ->emptyStateDescription('Wanna create your first layout now?')
            ->emptyStateActions([
                FilamentAction::make('create')
                    ->label('Create layout')
                    ->url(URL::route('filament.mailcarrier.resources.layouts.create'))
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
            'index' => Pages\ListLayouts::route('/'),
            'create' => Pages\CreateLayout::route('/create'),
            'edit' => Pages\EditLayout::route('/{record}/edit'),
        ];
    }

    /**
     * Get the form content.
     */
    protected static function getFormContent(): Section
    {
        return Section::make()
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Internal name')
                    ->required()
                    ->autofocus()
                    // Disable field UI if the record exists and user can't unlock it
                    ->disabled(fn (?Layout $record) => !is_null($record) && $record->is_locked)
                    // Save the field if record does not exist or user can unlock it
                    ->dehydrated(fn (?Layout $record) => is_null($record) || !$record->is_locked),

                CodeEditor::make('content')
                    ->language(Language::Html)
                    ->required()
                    ->hint(new HtmlString('<a href="https://twig.symfony.com/doc/3.x/templates.html" class="underline text-primary-500 cursor-help" target="_blank" tabindex="-1">Help with syntax</a>'))
                    ->hintIcon('heroicon-o-code-bracket-square')
                    ->columnSpanFull()
                    ->default(static::DEFAULT_CONTENT)
                    // Disable field UI if the record exists and user can't unlock it
                    ->disabled(fn (?Layout $record) => !is_null($record) && $record->is_locked)
                    // Save the field if record does not exist or user can unlock it
                    ->dehydrated(fn (?Layout $record) => is_null($record) || !$record->is_locked),
            ]);
    }

    /**
     * Get the form sidebar.
     */
    protected static function getFormSidebar(): Section
    {
        return Section::make()
            ->schema([
                Forms\Components\Toggle::make('is_locked')
                    ->inline(false)
                    // Disable field UI if the record exists and user can't unlock it
                    ->disabled(fn (?Layout $record) => !is_null($record) && !static::can('unlock', $record))
                    // Save the field if record does not exist or user can unlock it
                    ->dehydrated(fn (?Layout $record) => is_null($record) || static::can('unlock', $record)),

                Divider::make(),

                TextEntry::make('_layout_created_by')
                    ->label('Created by')
                    ->state(fn (?Layout $record): string => $record?->user?->getFilamentName() ?: '-'),

                ...Timestamps::make(),
            ]);
    }
}
