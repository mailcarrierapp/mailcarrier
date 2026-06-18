<?php

namespace MailCarrier\Dto;

use MailCarrier\Dto\Attributes\Strict;
use MailCarrier\Dto\Validators\Email;

#[Strict]
class ContactDto extends CastableDataTransferObject
{
    #[Email]
    public string $email;

    public ?string $name = null;

    /**
     * Try to create an instance from the given args.
     */
    public static function tryFrom(...$args): ?static
    {
        return rescue(fn () => new static(...$args), report: false); // @phpstan-ignore-line
    }

    /**
     * Convert the DTO into a string.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
