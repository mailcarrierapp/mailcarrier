<?php

namespace MailCarrier\Actions\Logs;

use Carbon\Carbon;
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
    public static function getData(): StatsOverviewDto
    {
        $totalNotPending = Log::query()
            ->whereNot('status', LogStatus::Pending)
            ->count('id');

        $sent = Log::query()
            ->where('status', LogStatus::Sent)
            ->count('id');

        $sentLastWeek = Log::query()
            ->toBase()
            ->selectRaw('COUNT(id) as count, DATE(created_at) as date')
            ->where('status', LogStatus::Sent)
            ->whereDate('created_at', '>', Carbon::now()->subWeek()->toDateString())
            ->groupByRaw('DATE(created_at)')
            ->get();

        $pending = Log::query()
            ->where('status', LogStatus::Pending)
            ->count('id');

        $failed = Log::query()
            ->where('status', LogStatus::Failed)
            ->count('id');

        $failedLastWeek = Log::query()
            ->toBase()
            ->selectRaw('COUNT(id) as count, DATE(created_at) as date')
            ->where('status', LogStatus::Failed)
            ->whereDate('created_at', '>', Carbon::now()->subWeek()->toDateString())
            ->groupByRaw('DATE(created_at)')
            ->get();

        $failurePercentage = $failed === 0 || $totalNotPending === 0 ? 0 : number_format($failed * 100 / $totalNotPending);

        // Fill last week data when missing
        $lastWeekRange = Carbon::today()->subDays(6)->daysUntil(Carbon::today());

        foreach ($lastWeekRange as $day) {
            if (!$sentLastWeek->firstWhere('date', $day->toDateString())) {
                $sentLastWeek->push([
                    'count' => 0,
                    'date' => $day->toDateString(),
                ]);
            }

            if (!$failedLastWeek->firstWhere('date', $day->toDateString())) {
                $failedLastWeek->push([
                    'count' => 0,
                    'date' => $day->toDateString(),
                ]);
            }
        }

        return new StatsOverviewDto(
            sent: $sent,
            pending: $pending,
            failed: $failed,
            failurePercentage: $failurePercentage,
            sentLastWeek: $sentLastWeek->sortBy('date')->pluck('count')->all(),
            failedLastWeek: $failedLastWeek->sortBy('date')->pluck('count')->all(),
        );
    }
}
