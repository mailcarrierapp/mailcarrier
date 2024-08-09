<?php

namespace MailCarrier\Models\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class DataTransferObject implements CastsAttributes
{
    public function __construct(
        /** @var string The DataTransferObject class to cast to */
        protected string $class,
        /** @var array The cast parameters specified */
        protected array $parameters = [],
    ) {
        //
    }

    /**
     * Cast the stored value to the configured DataTransferObject.
     */
    public function get($model, string $key, $value, array $attributes)
    {
        if (is_null($value) && in_array('nullable', $this->parameters)) {
            $value = '{}';
        }

        if (is_null($value)) {
            return;
        }

        return $this->class::fromJson($value, $this->getJsonFlags()->decode);
    }

    /**
     * Prepare the given value for storage.
     */
    public function set($model, string $key, $value, array $attributes)
    {
        if (is_null($value)) {
            return;
        }

        if (is_array($value)) {
            $value = new $this->class($value);
        }

        if (!$value instanceof $this->class) {
            throw new \InvalidArgumentException("Value must be of type [$this->class], array, or null");
        }

        return $value->toJson($this->getJsonFlags()->encode);
    }

    protected function getJsonFlags(): CastUsingJsonFlags
    {
        $attributes = (new \ReflectionClass($this->class))
            ->getAttributes(CastUsingJsonFlags::class);

        return ($attributes[0] ?? null)?->newInstance()
            ?? new CastUsingJsonFlags;
    }
}
