<?php

namespace MailCarrier\Actions\Logs;

use Illuminate\Support\Facades\Cache;
use MailCarrier\Actions\Action;
use MailCarrier\Models\Log;

class GetTriggers extends Action
{
    /**
     * Get the unique triggers from sent mails.
     */
    public function run(): array
    {
        return Cache::rememberForever(
            static::class,
            fn () => Log::query()
                ->select('trigger')
                ->distinct()
                ->orderBy('trigger')
                ->get()
                ->pluck('trigger')
                ->filter() // Remove null values
                ->mapWithKeys(fn (string $value) => [$value => $value])
                ->toArray()
        );
    }

    /**
     * Flush the class cache.
     */
    public static function flush(): void
    {
        Cache::forget(static::class);
    }
}
