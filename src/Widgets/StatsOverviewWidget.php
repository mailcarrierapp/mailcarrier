<?php

namespace MailCarrier\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use MailCarrier\Actions\Logs;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getCards(): array
    {
        $data = Logs\GetStatsOverview::resolve()->run();

        return [
            Card::make('Total sent', $data->sent)
                ->icon('heroicon-s-sparkles')
                ->chart($data->sentLastWeek)
                ->color('success'),

            Card::make('Pending', $data->pending)
                ->icon('heroicon-s-collection')
                ->color('warning'),

            Card::make('Total errors', $data->failed)
                ->description($data->failurePercentage . '% of total emails')
                ->icon('heroicon-s-thumb-down')
                ->chart($data->failedLastWeek)
                ->color('danger'),
        ];
    }
}
