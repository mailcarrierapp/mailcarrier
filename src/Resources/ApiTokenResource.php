<?php

namespace MailCarrier\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Laravel\Sanctum\PersonalAccessToken;
use MailCarrier\Resources\ApiTokenResource\Pages;

class ApiTokenResource extends Resource
{
    protected static ?string $model = PersonalAccessToken::class;

    protected static ?string $modelLabel = 'API Tokens';

    protected static ?string $navigationIcon = 'heroicon-o-key';

    protected static ?string $navigationGroup = 'Management';

    /**
     * List all the records.
     */
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->placeholder('No name provided'),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expiration date (UTC)')
                    ->dateTime()
                    ->placeholder('Never expires'),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->dateTime()
                    ->placeholder('Never used'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
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
