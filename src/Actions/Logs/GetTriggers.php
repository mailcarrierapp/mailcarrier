<?php

namespace MailCarrier\MailCarrier\Actions\Logs;

use MailCarrier\MailCarrier\Actions\Action;
use MailCarrier\MailCarrier\Models\Log;
use Illuminate\Support\Facades\Cache;

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
