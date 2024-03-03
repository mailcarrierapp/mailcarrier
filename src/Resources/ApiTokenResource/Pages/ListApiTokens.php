<?php

namespace MailCarrier\Resources\ApiTokenResource\Pages;

use Filament\Resources\Pages\ListRecords;
use MailCarrier\Resources\ApiTokenResource;

class ListApiTokens extends ListRecords
{
    protected static string $resource = ApiTokenResource::class;
}
