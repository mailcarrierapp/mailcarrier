<?php

namespace MailCarrier\Schemas\Components;

use Filament\Schemas\Components\Component;
use Filament\Support\Components\Contracts\HasEmbeddedView;

class Divider extends Component implements HasEmbeddedView
{
    public static function make(): static
    {
        $static = app(static::class);
        $static->configure();

        return $static;
    }

    public function toEmbeddedHtml(): string
    {
        return '<div class="h-px w-full bg-gray-100 dark:bg-gray-700"></div>';
    }
}
