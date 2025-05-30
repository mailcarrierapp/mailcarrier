<?php

namespace MailCarrier\Resources\LogResource\Pages;

use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Models\Log;
use MailCarrier\Resources\LogResource;

class ListLogs extends ListRecords
{
    protected static string $resource = LogResource::class;

    /**
     * Fetch the layouts list.
     */
    protected function getTableQuery(): EloquentBuilder
    {
        return Log::query()
            ->with(['template', 'attachments', 'events'])
            ->latest();
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make()
                ->icon('heroicon-o-paper-airplane'),
            'pending' => Tab::make()
                ->modifyQueryUsing(fn (EloquentBuilder $query) => $query->where('status', LogStatus::Pending))
                ->icon('heroicon-o-clock')
                ->badge(Log::query()->where('status', LogStatus::Pending)->count()),
            'failed' => Tab::make()
                ->modifyQueryUsing(fn (EloquentBuilder $query) => $query->where('status', LogStatus::Failed))
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
