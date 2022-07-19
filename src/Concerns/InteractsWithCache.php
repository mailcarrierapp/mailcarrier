<?php

namespace MailCarrier\MailCarrier\Concerns;

use Carbon\Carbon;
use Illuminate\Cache\CacheManager;
use Illuminate\Cache\TaggedCache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

trait InteractsWithCache
{
    /**
     * The cache tag to be used.
     * Useful to clean up data after an operation.
     */
    protected string|array|null $cacheTag = null;

    /**
     * Choose whether or not to skip the cache for the next request.
     */
    protected bool $skipCache = false;

    /**
     * Original args to build the cache key.
     */
    protected array $cacheKeyArgs = [];

    /**
     * Set the cache key args.
     */
    public function withCacheArgs(array $args): static
    {
        $this->cacheKeyArgs = $args;

        return $this;
    }

    /**
     * Retrieve the fresh data skipping the cache.
     */
    public function withoutCache(): static
    {
        $this->skipCache = true;

        return $this;
    }

    /**
     * Retrieve the fresh data skipping the cache.
     */
    public function usingCacheTags(string|array $tags): static
    {
        $this->cacheTag = $tags;

        return $this;
    }

    /**
     * Get a cached instance of the results.
     */
    protected function cached(\Closure $callback): mixed
    {
        if ($this->skipCache) {
            $this->skipCache = false;

            return $callback();
        }

        return $this
            ->getCacheInstance()
            ->rememberForever($this->getCacheKey(), $callback);
    }

    /**
     * Get a cached (until midnight) instance of the results.
     */
    protected function cachedUntilMidnight(\Closure $callback): mixed
    {
        if ($this->skipCache) {
            $this->skipCache = false;

            return $callback();
        }

        return $this
            ->getCacheInstance()
            ->remember(
                $this->getCacheKey(),
                Carbon::tomorrow(),
                $callback,
            );
    }

    /**
     * Get a cached (until midnight) instance of the results.
     */
    protected function cachedUntil(\Closure|\DateTimeInterface|\DateInterval|int|null $ttl, \Closure $callback): mixed
    {
        if ($this->skipCache) {
            $this->skipCache = false;

            return $callback();
        }

        return $this
            ->getCacheInstance()
            ->remember(
                $this->getCacheKey(),
                $ttl,
                $callback,
            );
    }

    /**
     * Forget the resource.
     */
    protected function forget(): void
    {
        $this
            ->getCacheInstance()
            ->forget($this->getCacheKey());
    }

    /**
     * Get a cache instance.
     */
    protected function getCacheInstance(): TaggedCache|CacheManager
    {
        if (method_exists(Cache::getStore(), 'tags')) {
            return Cache::tags($this->cacheTag ?: static::class);
        }

        return Cache::getFacadeRoot();
    }

    /**
     * Get the cache key for a given set of arguments.
     */
    protected function getCacheKey(): string
    {
        $key = static::class;

        if (!empty($this->cacheKeyArgs)) {
            $sanitizedArgs = Collection::make($this->cacheKeyArgs)
                // Exclude callables and closures from serialization
                ->filter(fn (mixed $arg) => !is_callable($arg))
                ->map(function (mixed $arg): mixed {
                    // Get the primary key of the model
                    if ($arg instanceof Model) {
                        return $arg::class . $arg->getKey();
                    }

                    return $arg;
                })
                ->all();

            $key .= '_' . md5(json_encode($sanitizedArgs));
        }

        return $key;
    }
}
