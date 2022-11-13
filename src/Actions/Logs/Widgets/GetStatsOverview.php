<?php

namespace MailCarrier\Actions\Logs\Widgets;

use Flowframe\Trend\Trend;
use Illuminate\Support\Carbon;
use MailCarrier\Actions\Action;
use MailCarrier\Concerns\InteractsWithCache;
use MailCarrier\Dto\Dashboard\StatsOverviewDto;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Models\Log;

class GetStatsOverview extends Action
{
    use InteractsWithCache;

    /**
     * Generate a unique slug from the given name.
     */
    public function run(): StatsOverviewDto
    {
        return $this->cachedUntil(Carbon::now()->addMinutes(30), $this->getData(...));
    }

    /**
     * Flush the template cache by its slug.
     */
    protected function getData(): StatsOverviewDto
    {
        $totalNotPending = Log::query()
            ->whereNot('status', LogStatus::Pending)
            ->count('id');

        $sent = Log::query()
            ->where('status', LogStatus::Sent)
            ->count('id');

        $sentLastWeek = Trend::query(Log::query()->where('status', LogStatus::Sent))
            ->between(
                start: Carbon::today()->subDays(6),
                end: Carbon::today()->endOfDay(),
            )
            ->perDay()
            ->count();

        $pending = Log::query()
            ->where('status', LogStatus::Pending)
            ->count('id');

        $failed = Log::query()
            ->where('status', LogStatus::Failed)
            ->count('id');

        $failedLastWeek = Trend::query(Log::query()->where('status', LogStatus::Failed))
            ->between(
                start: Carbon::today()->subDays(6),
                end: Carbon::today()->endOfDay(),
            )
            ->perDay()
            ->count();

        $failurePercentage = rescue(fn () => number_format($failed * 100 / $totalNotPending), rescue: 0, report: false);

        return new StatsOverviewDto(
            sent: $sent,
            pending: $pending,
            failed: $failed,
            failurePercentage: $failurePercentage,
            sentLastWeek: $sentLastWeek->pluck('aggregate')->all(),
            failedLastWeek: $failedLastWeek->pluck('aggregate')->all(),
        );
    }
}
