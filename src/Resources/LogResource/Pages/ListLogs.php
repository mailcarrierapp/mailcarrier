<?php

namespace MailCarrier\MailCarrier\Resources\LogResource\Pages;

use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use MailCarrier\MailCarrier\Models\Log;
use MailCarrier\MailCarrier\Resources\LogResource;

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
