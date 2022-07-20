<?php

namespace MailCarrier\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait EnumSerializable
{
    /**
     * Get the array of enum's values.
     */
    public static function toEntries(): array
    {
        return Collection::make(static::cases())
            ->mapWithKeys(fn (mixed $enum) => [
                $enum->value => Str::title($enum->value),
            ])
            ->all();
    }
}
