<?php

namespace MailCarrier\Dto;

use MailCarrier\Dto\Attributes\Strict;

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
