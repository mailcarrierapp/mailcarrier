<?php

namespace MailCarrier\MailCarrier\Dto\Casters;

use MailCarrier\MailCarrier\Dto\ContactDto;
use Spatie\DataTransferObject\Caster;

class ContactStringCaster implements Caster
{
    /**
     * Cast the value to a ContactDto or null.
     */
    public function cast(mixed $value): ?ContactDto
    {
        if (is_null($value)) {
            return null;
        }

        if ($value instanceof ContactDto) {
            return $value;
        }

        if (!is_array($value)) {
            $value = [
                'email' => $value,
            ];
        }

        return new ContactDto($value);
    }
}
