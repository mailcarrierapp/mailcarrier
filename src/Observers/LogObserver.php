<?php

namespace MailCarrier\Observers;

use MailCarrier\Actions\Widgets\GetSentFailureStats;
use MailCarrier\Actions\Widgets\GetStatsOverview;
use MailCarrier\Actions\Widgets\GetTopTriggers;

class LogObserver
{
    /**
     * Handle the Log "created" event.
     */
    public function created(): void
    {
        $this->flushCache();
    }

    /**
     * Handle the Log "updated" event.
     */
    public function updated(): void
    {
        $this->flushCache();
    }

    /**
     * Handle the Log "deleted" event.
     */
    public function deleted(): void
    {
        $this->flushCache();
    }

    /**
     * Clear the dashboard cache.
     */
    protected function flushCache(): void
    {
        GetSentFailureStats::flush();
        GetStatsOverview::flush();
        GetTopTriggers::flush();
    }
}
