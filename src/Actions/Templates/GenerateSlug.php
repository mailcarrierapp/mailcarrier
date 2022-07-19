<?php

namespace MailCarrier\MailCarrier\Actions\Templates;

use Illuminate\Support\Str;
use MailCarrier\MailCarrier\Actions\Action;
use MailCarrier\MailCarrier\Models\Template;

class GenerateSlug extends Action
{
    /**
     * Generate a unique slug from the given name.
     */
    public function run(string $name, int $maxLength = 255): string
    {
        $retries = 0;
        $slug = Str::of($name)
            ->slug()
            ->limit($maxLength, '')
            ->toString();

        while (Template::query()->where('slug', $slug)->exists()) {
            $retries++;
            $slug = Str::of($name)
                ->slug()
                // Always reach the desired max length with the retries number and the dash
                ->limit($maxLength - strlen((string) $retries) - 1, '')
                ->append('-'.$retries)
                ->toString();
        }

        return $slug;
    }
}
