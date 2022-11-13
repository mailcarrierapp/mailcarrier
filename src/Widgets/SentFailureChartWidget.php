<?php

namespace MailCarrier\Widgets;

use Carbon\Carbon;
use Filament\Widgets\LineChartWidget;
use MailCarrier\Actions\Logs\GetSentFailureStats;
use MailCarrier\Enums\SentFailureChartFilter;

class SentFailureChartWidget extends LineChartWidget
{
    protected static ?string $pollingInterval = null;

    public ?string $filter = 'today';


    protected function getHeading(): string
    {
        return 'Sent and failure over time';
    }

    protected function getFilters(): ?array
    {
        return [
            SentFailureChartFilter::Today->value => 'Today',
            SentFailureChartFilter::Week->value => 'Last week',
            SentFailureChartFilter::Month->value => 'Last month',
            SentFailureChartFilter::Year->value => 'This year',
        ];
    }

    protected function getData(): array
    {
        $filter = SentFailureChartFilter::from($this->filter);
        $data = GetSentFailureStats::resolve()->run($filter);

        $labelFormat = match ($filter) {
            SentFailureChartFilter::Today => 'H:i',
            SentFailureChartFilter::Week => 'd M',
            SentFailureChartFilter::Month => 'd M',
            SentFailureChartFilter::Year => 'M',
        };

        return [
            'datasets' => [
                [
                    'backgroundColor' => '#22c55e',
                    'borderColor' => '#22c55e',
                    'label' => 'Sent',
                    'data' => $data->sent->pluck('aggregate'),
                ],
                [
                    'backgroundColor' => '#dc2626',
                    'borderColor' => '#dc2626',
                    'label' => 'Failure',
                    'data' => $data->failure->pluck('aggregate'),
                ],
            ],
            'labels' => $data
                ->sent
                ->pluck('date')
                ->map(fn (string $date) => Carbon::parse($date)->format($labelFormat)),
        ];
    }
}
