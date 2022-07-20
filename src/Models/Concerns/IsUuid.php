<?php

namespace MailCarrier\Models\Concerns;

use Illuminate\Support\Str;

trait IsUuid
{
    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        // Generate a UUID for primary key if not provided (could be set by a factory)
        static::creating(function (self $model): void {
            /** @var string $primaryKey */
            $primaryKey = $model->getKeyName();

            if (!$model->getAttributeValue($primaryKey)) {
                $model->setAttribute($primaryKey, (string) Str::uuid());
            }
        });
    }
}
