<?php

namespace MailCarrier\Dto\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class CastWith
{
    public readonly array $args;

    public function __construct(
        public readonly string $casterClass,
        mixed ...$args
    ) {
        $this->args = $args;
    }
}
