<?php

namespace MailCarrier\Resources;

use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;
use MailCarrier\Enums\Auth;
use MailCarrier\Models\User;
use MailCarrier\Resources\UserResource\Pages;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

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

                Tables\Columns\TextColumn::make('email')
                    ->formatStateUsing(
                        fn (string $state) => Str::of($state)
                            ->when(
                                Str::of($state)->before('@')->length() > 5,
                                fn (Stringable $str) => $str->mask('*', 3, strpos($state, '@') - 3),
                                fn (Stringable $str) => $str->mask('*', 0, strpos($state, '@')),
                            )
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creation date')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->query(User::query()->whereNot('email', Auth::AuthManagerEmail->value));
    }

    /**
     * Get Filament CRUD pages.
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
        ];
    }
}
