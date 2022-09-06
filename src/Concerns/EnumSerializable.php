<?php

namespace MailCarrier\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait EnumSerializable
{
    /**
     * Get an array with only the names of the enum.
     */
    public static function toNames(): array
    {
        return array_map(
            fn (self $case) => $case->name,
            self::cases()
        );
    }

    /**
     * Get an array with only the values of the enum.
     */
    public static function toValues(): array
    {
        return array_map(
            fn (self $case) => $case->value,
            self::cases()
        );
    }

    /**
     * Get a pair of name => value.
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
