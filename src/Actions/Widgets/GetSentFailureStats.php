<?php

namespace MailCarrier\Actions\Widgets;

use Flowframe\Trend\Trend;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use MailCarrier\Actions\Action;
use MailCarrier\Dto\Dashboard\SentFailureChartDto;
use MailCarrier\Enums\Dashboard\SentFailureChartFilter;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Models\Log;

class GetSentFailureStats extends Action
{
    protected SentFailureChartFilter $filter;

    /**
     * Generate a unique slug from the given name.
     */
    public function run(SentFailureChartFilter $filter): SentFailureChartDto
    {
        $this->filter = $filter;

        return Cache::rememberForever(
            'dashboard:sent-failure.' . $filter->value,
            $this->getData(...)
        );
    }

    /**
     * Flush the action cache.
     */
    public static function flush(): void
    {
        foreach (SentFailureChartFilter::cases() as $filter) {
            Cache::forget(
                'dashboard:sent-failure.' . $filter->value,
            );
        }
    }

    /**
     * Flush the template cache by its slug.
     */
    protected function getData(): SentFailureChartDto
    {
        $start = match ($this->filter) {
            SentFailureChartFilter::Today => Carbon::today(),
            SentFailureChartFilter::Week => Carbon::today()->subDays(6),
            SentFailureChartFilter::Month => Carbon::today()->subMonth(),
            SentFailureChartFilter::Year => Carbon::today()->startOfYear(),
        };

        return new SentFailureChartDto(
            sent: $this->getStatusData(LogStatus::Sent, $start),
            failure: $this->getStatusData(LogStatus::Failed, $start)
        );
    }

    /**
     * Get single data per-status statistics.
     */
    protected function getStatusData(LogStatus $status, Carbon $start): Collection
    {
        $data = Trend::query(Log::query()->where('status', $status))
            ->between(
                start: $start,
                end: Carbon::today()->endOfDay(),
            );

        $data = match ($this->filter) {
            SentFailureChartFilter::Today => $data->perHour(),
            SentFailureChartFilter::Week => $data->perDay(),
            SentFailureChartFilter::Month => $data->perDay(),
            SentFailureChartFilter::Year => $data->perMonth(),
        };

        return $data->count('id');
    }
}
