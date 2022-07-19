<?php

namespace MailCarrier\MailCarrier\Resources\LogResource\Pages;

use MailCarrier\MailCarrier\Resources\LogResource;
use MailCarrier\MailCarrier\Models\Log;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class ListLogs extends ListRecords
{
    protected static string $resource = LogResource::class;

    /**
     * Fetch the layouts list.
     */
    protected function getTableQuery(): EloquentBuilder
    {
        return Log::query()
            ->with('template')
            ->latest();
    }
}
