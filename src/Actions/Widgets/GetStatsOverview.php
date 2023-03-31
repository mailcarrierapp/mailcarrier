<?php

namespace MailCarrier\Actions\Widgets;

use Flowframe\Trend\Trend;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use MailCarrier\Actions\Action;
use MailCarrier\Dto\Dashboard\StatsOverviewDto;
use MailCarrier\Enums\LogStatus;
use MailCarrier\Models\Log;

class GetStatsOverview extends Action
{
    /**
     * Generate a unique slug from the given name.
     */
    public function run(): StatsOverviewDto
    {
        return Cache::rememberForever('dashboard:overview', $this->getData(...));
    }

    /**
     * Flush the action cache.
     */
    public static function flush(): void
    {
        Cache::forget('dashboard:overview');
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
            ->count('id');

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
            ->count('id');

        /** @var int */
        $failurePercentage = rescue(
            fn () => (int) round($failed * 100 / $totalNotPending),
            rescue: 0,
            report: false
        );

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
