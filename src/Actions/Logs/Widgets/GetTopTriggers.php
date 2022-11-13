<?php

namespace MailCarrier\Actions\Logs\Widgets;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MailCarrier\Actions\Action;
use MailCarrier\Concerns\InteractsWithCache;
use MailCarrier\Models\Log;

class GetTopTriggers extends Action
{
    use InteractsWithCache;

    /**
     * Generate a unique slug from the given name.
     */
    public function run(): Collection
    {
        return $this
            ->withCacheArgs(func_get_args())
            ->cachedUntil(Carbon::now()->addMinutes(30), $this->getData(...));
    }

    /**
     * Flush the template cache by its slug.
     */
    protected function getData(): Collection
    {
        return Log::query()
            ->toBase()
            ->select(['trigger', DB::raw('COUNT(id) as count')])
            ->whereNotNull('trigger')
            ->whereNot('trigger', '')
            ->groupBy('trigger')
            ->orderByRaw('COUNT(id) DESC')
            ->get();
    }
}
