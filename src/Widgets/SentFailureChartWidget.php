<?php

namespace MailCarrier\Widgets;

use Carbon\Carbon;
use Filament\Widgets\LineChartWidget;
use MailCarrier\Actions\Logs\Widgets\GetSentFailureStats;
use MailCarrier\Enums\Dashboard\SentFailureChartFilter;

class SentFailureChartWidget extends LineChartWidget
{
    protected static ?string $pollingInterval = null;

    protected int|string|array $columnSpan = 2;

    public ?string $filter = 'today';

    /**
     * Get widget title.
     */
    protected function getHeading(): string
    {
        return 'Sent and failure over time';
    }

    /**
     * Get chart filters.
     */
    protected function getFilters(): ?array
    {
        return [
            SentFailureChartFilter::Today->value => 'Today',
            SentFailureChartFilter::Week->value => 'Last week',
            SentFailureChartFilter::Month->value => 'Last month',
            SentFailureChartFilter::Year->value => 'This year',
        ];
    }

    /**
     * Get chart data.
     */
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
