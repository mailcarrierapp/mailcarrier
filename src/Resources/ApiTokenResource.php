<?php

namespace MailCarrier\Resources;

use Carbon\CarbonInterface;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Actions\Action as TablesAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use MailCarrier\Actions\Logs\GetTriggers;
use MailCarrier\Actions\SendMail;
use MailCarrier\Dto\AttachmentDto;
use MailCarrier\Dto\LogTemplateDto;
use MailCarrier\Dto\SendMailDto;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Facades\MailCarrier;
use MailCarrier\Models\Attachment;
use MailCarrier\Models\Log;
use MailCarrier\Models\Template;
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
