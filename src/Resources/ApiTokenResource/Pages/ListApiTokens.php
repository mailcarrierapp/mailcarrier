<?php

namespace MailCarrier\Resources\ApiTokenResource\Pages;

use Filament\Resources\Pages\ListRecords;
use MailCarrier\Resources\ApiTokenResource;
use MailCarrier\Resources\ApiTokenResource\Actions\CreateAction;

class ListApiTokens extends ListRecords
{
    protected static string $resource = ApiTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
