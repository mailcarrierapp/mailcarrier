<?php

namespace MailCarrier\Dto\Casters;

use Illuminate\Support\Collection;
use MailCarrier\Dto\ContactDto;
use Spatie\DataTransferObject\Caster;

class ContactArrayCaster implements Caster
{
    public function cast(mixed $value): ?array
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof ContactDto) {
            return [$value];
        }

        if (!is_array($value)) {
            $value = [
                'email' => $value,
            ];
        }

        if (Collection::make($value)->every(fn (mixed $value) => $value instanceof ContactDto)) {
            return $value;
        }

        if (is_array($value) && array_is_list($value) && is_string($value[0])) {
            $value = array_map(
                fn (string $item) => [
                    'email' => $item,
                ],
                $value
            );
        }

        if (!array_is_list($value)) {
            $value = [$value];
        }

        return array_map(
            fn (array $item) => new ContactDto($item),
            $value
        );
    }
}
