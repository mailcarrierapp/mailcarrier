<?php

namespace MailCarrier\Dto\Casters;

use MailCarrier\Dto\Contracts\Caster;

class ArrayCaster implements Caster
{
    public function __construct(
        protected readonly string $itemType,
    ) {}

    public function cast(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_map(
            fn (mixed $item) => $item instanceof $this->itemType ? $item : new $this->itemType($item),
            $value
        );
    }
}
