<?php

namespace MailCarrier\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use MailCarrier\Actions\Widgets\GetStatsOverview;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        $data = GetStatsOverview::resolve()->run();

        return [
            Stat::make('Total sent', $data->sent)
                ->icon('heroicon-o-signal')
                ->chart($data->sent === 0 ? null : $data->sentLastWeek)
                ->color('success'),

            Stat::make('Pending', $data->pending)
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Total errors', $data->failed)
                ->description($data->failed === 0 ? null : $data->failurePercentage . '% of total emails')
                ->icon('heroicon-o-exclamation-triangle')
                ->chart($data->failed === 0 ? null : $data->failedLastWeek)
                ->color('danger'),
        ];
    }
}
