<?php

namespace MailCarrier\MailCarrier\Actions\Templates;

use MailCarrier\MailCarrier\Actions\Action;
use MailCarrier\MailCarrier\Concerns\InteractsWithCache;
use MailCarrier\MailCarrier\Models\Template;

class FindBySlug extends Action
{
    use InteractsWithCache;

    /**
     * Generate a unique slug from the given name.
     */
    public function run(string $slug): Template
    {
        return $this
            ->usingCacheTags(Template::class)
            ->withCacheArgs(func_get_args())
            ->cached(
                fn () => Template::query()
                        ->where('slug', $slug)
                        ->firstOrFail()
            );
    }

    /**
     * Flush the template cache by its slug.
     */
    public static function flush(string $slug): void
    {
        static::resolve()
            ->usingCacheTags(Template::class)
            ->withCacheArgs(func_get_args())
            ->forget();
    }
}
