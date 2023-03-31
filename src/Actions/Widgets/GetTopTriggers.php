<?php

namespace MailCarrier\Actions\Widgets;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use MailCarrier\Actions\Action;
use MailCarrier\Models\Log;

class GetTopTriggers extends Action
{
    /**
     * Generate a unique slug from the given name.
     */
    public function run(): Collection
    {
        return Cache::rememberForever('dashboard:top-triggers', $this->getData(...));
    }

    /**
     * Flush the action cache.
     */
    public static function flush(): void
    {
        Cache::forget('dashboard:top-triggers');
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
