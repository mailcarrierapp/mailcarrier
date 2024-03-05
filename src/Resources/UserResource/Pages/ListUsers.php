<?php

namespace MailCarrier\Resources\UserResource\Pages;

use Filament\Resources\Pages\ListRecords;
use MailCarrier\Resources\UserResource;
use MailCarrier\Resources\UserResource\Actions\CreateAction;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;


    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
