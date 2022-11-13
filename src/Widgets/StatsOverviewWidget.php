<?php

namespace MailCarrier\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use MailCarrier\Actions\Logs;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    /**
     * Get widget cards.
     */
    protected function getCards(): array
    {
        $data = Logs\GetStatsOverview::resolve()->run();

        return [
            Card::make('Total sent', $data->sent)
                ->icon('heroicon-s-sparkles')
                ->chart($data->sent === 0 ? null : $data->sentLastWeek)
                ->color('success'),

            Card::make('Pending', $data->pending)
                ->icon('heroicon-s-collection')
                ->color('warning'),

            Card::make('Total errors', $data->failed)
                ->description($data->failed === 0 ? null : $data->failurePercentage . '% of total emails')
                ->icon('heroicon-s-thumb-down')
                ->chart($data->failed === 0 ? null : $data->failedLastWeek)
                ->color('danger'),
        ];
    }
}
