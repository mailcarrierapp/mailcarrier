<?php

namespace MailCarrier\Resources;

use Filament\Actions\DeleteAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Laravel\Sanctum\PersonalAccessToken;
use MailCarrier\Resources\ApiTokenResource\Pages;

class ApiTokenResource extends Resource
{
    protected static ?string $model = PersonalAccessToken::class;

    protected static ?string $modelLabel = 'API Tokens';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static string|\UnitEnum|null $navigationGroup = 'Management';

    /**
     * List all the records.
     */
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->placeholder('No name provided'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expiration date (UTC)')
                    ->dateTime()
                    ->placeholder('Never expires')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->dateTime()
                    ->placeholder('Never used')
                    ->sortable(),
            ])
            ->recordActions([
                DeleteAction::make(),
            ]);
    }

    /**
     * Get Filament CRUD pages.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListApiTokens::route('/'),
        ];
    }
}
