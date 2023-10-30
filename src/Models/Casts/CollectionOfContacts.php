<?php

namespace MailCarrier\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use MailCarrier\Dto\ContactDto;

class CollectionOfContacts implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     * @return \Illuminate\Support\Collection<\MailCarrier\Dto\ContactDto>|null
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Collection
    {
        if (is_null($value)) {
            return null;
        }

        return Collection::make(json_decode($value, true))
            ->map(fn (array $value) => new ContactDto($value));
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        return json_encode($value);
    }
}
