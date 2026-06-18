<?php

namespace MailCarrier\Dto\Contracts;

interface Caster
{
    public function cast(mixed $value): mixed;
}
