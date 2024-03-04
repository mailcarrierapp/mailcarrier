<?php

namespace MailCarrier\Resources\UserResource\Pages;

use Filament\Resources\Pages\ListRecords;
use MailCarrier\Resources\UserResource;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
}
