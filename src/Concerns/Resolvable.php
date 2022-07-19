<?php

namespace MailCarrier\MailCarrier\Concerns;

use Illuminate\Support\Facades\App;

trait Resolvable
{
    /**
     * Resolve itself from the container.
     */
    public static function resolve(array $parameters = []): static
    {
        return App::make(static::class, $parameters);
    }
}
