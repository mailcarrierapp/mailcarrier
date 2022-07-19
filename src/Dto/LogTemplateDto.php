<?php

namespace MailCarrier\MailCarrier\Dto;

use JessArcher\CastableDataTransferObject\CastableDataTransferObject;
use Spatie\DataTransferObject\Attributes\Strict;

#[Strict]
class LogTemplateDto extends CastableDataTransferObject
{
    public string $name;

    public ?string $render;

    public string $hash;

    /**
     * Convert the DTO into a string.
     */
    public function __toString(): string
    {
        return $this->toJson();
    }
}
