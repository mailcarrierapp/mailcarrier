<?php

namespace MailCarrier\Resources;

use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;
use Illuminate\Support\HtmlString;
use MailCarrier\Forms\Components\MonacoEditor;
use MailCarrier\Models\Layout;
use MailCarrier\Models\User;
use MailCarrier\Resources\LayoutResource\Pages;
use RalphJSmit\Filament\Components\Forms\Sidebar;
use RalphJSmit\Filament\Components\Forms\Timestamps;

class LayoutResource extends Resource
{
    protected static ?string $model = Layout::class;

    protected static ?string $navigationIcon = 'heroicon-o-color-swatch';

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
                {include content}
            </body>
        </html>
        HTML;

    /**
     * Build the form.
     */
    public static function form(Form $form): Form
    {
        return Sidebar::make($form)->schema(
            static::getFormContent(),
            static::getFormSidebar()
        );
    }

    /**
     * List all the records.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('templates_count'),

                Tables\Columns\TextColumn::make('user')
                    ->label('User')
                    ->formatStateUsing(fn (?User $state) => $state?->getFilamentName() ?: '-'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
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
    protected static function getFormContent(): array
    {
        return [
            Forms\Components\Card::make([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->autofocus()
                    // Disable field UI if the record exists and user can't unlock it
                    ->disabled(fn (?Layout $record) => !is_null($record) && !static::can('unlock', $record))
                    // Save the field if record does not exist or user can unlock it
                    ->dehydrated(fn (?Layout $record) => is_null($record) || static::can('unlock', $record)),

                MonacoEditor::make('content')
                    ->required()
                    ->hint('<a href="https://latte.nette.org/en/syntax" class="underline text-primary-500 cursor-help" target="_blank" tabindex="-1">Help with template Syntax</a>')
                    ->hintIcon('heroicon-o-code')
                    // Full width
                    ->columnSpan(2)
                    ->default(static::DEFAULT_CONTENT)
                    // Disable field UI if the record exists and user can't unlock it
                    ->disabled(fn (?Layout $record) => !is_null($record) && !static::can('unlock', $record))
                    // Save the field if record does not exist or user can unlock it
                    ->dehydrated(fn (?Layout $record) => is_null($record) || static::can('unlock', $record)),
            ]),
        ];
    }

    /**
     * Get the form sidebar.
     */
    protected static function getFormSidebar(): array
    {
        return [
            Forms\Components\Card::make([
                Forms\Components\Toggle::make('is_locked')
                    ->inline(false)
                    // Disable field UI if the record exists and user can't unlock it
                    ->disabled(fn (?Layout $record) => !is_null($record) && !static::can('unlock', $record))
                    // Save the field if record does not exist or user can unlock it
                    ->dehydrated(fn (?Layout $record) => is_null($record) || static::can('unlock', $record)),

                Forms\Components\Group::make([
                    Forms\Components\Placeholder::make('Separator')
                        ->label('')
                        ->content(new HtmlString('<div class="h-1 border-b border-gray-100 dark:border-gray-700"></div>')),

                    Forms\Components\Placeholder::make('Created by')
                        ->content(fn (?Layout $record) => $record?->user?->getFilamentName() ?: '-'),

                    ...Timestamps::make(),
                ])->when(fn (?Layout $record) => !is_null($record)),
            ]),
        ];
    }
}
