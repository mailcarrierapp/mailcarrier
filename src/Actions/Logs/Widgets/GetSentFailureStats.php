<?php

namespace MailCarrier\Actions\Logs\Widgets;

use Flowframe\Trend\Trend;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use MailCarrier\Actions\Action;
use MailCarrier\Concerns\InteractsWithCache;
use MailCarrier\Dto\Dashboard\SentFailureChartDto;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Enums\SentFailureChartFilter;
use MailCarrier\Models\Log;

class GetSentFailureStats extends Action
{
    use InteractsWithCache;

    protected SentFailureChartFilter $filter;

    /**
     * Generate a unique slug from the given name.
     */
    public function run(SentFailureChartFilter $filter): SentFailureChartDto
    {
        $this->filter = $filter;

        return $this
            ->withCacheArgs(func_get_args())
            ->cachedUntil(Carbon::now()->addMinutes(30), $this->getData(...));
    }

    /**
     * Flush the template cache by its slug.
     */
    protected function getData(): SentFailureChartDto
    {
        $start = match ($this->filter) {
            SentFailureChartFilter::Today => Carbon::today(),
            SentFailureChartFilter::Week => Carbon::today()->subWeek()->addDay(),
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
        $sent = Trend::query(Log::query()->where('status', $status))
            ->between(
                start: $start,
                end: Carbon::today()->endOfDay(),
            );

        $sent = match ($this->filter) {
            SentFailureChartFilter::Today => $sent->perHour(),
            SentFailureChartFilter::Week => $sent->perDay(),
            SentFailureChartFilter::Month => $sent->perDay(),
            SentFailureChartFilter::Year => $sent->perMonth(),
        };

        return $sent->count();
    }
}
