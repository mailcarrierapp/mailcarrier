<?php

namespace MailCarrier\Dto;

use MailCarrier\Dto\Attributes\CastWith;
use MailCarrier\Dto\Attributes\Strict;
use MailCarrier\Dto\Contracts\Validator;

abstract class DataTransferObject
{
    public function __construct(mixed ...$args)
    {
        // Support both positional array construction (new Foo(['key' => 'val']))
        // and named-argument construction (new Foo(key: 'val')).
        if (count($args) === 1 && array_key_first($args) === 0 && is_array($args[0])) {
            $args = $args[0];
        }

        $class = new \ReflectionClass($this);
        $isStrict = !empty($class->getAttributes(Strict::class));

        foreach ($args as $key => $value) {
            if (!$class->hasProperty($key)) {
                if ($isStrict) {
                    throw new \InvalidArgumentException(sprintf(
                        'Property [%s] does not exist on [%s].',
                        $key,
                        static::class
                    ));
                }

                continue;
            }

            $property = $class->getProperty($key);
            $castWithAttributes = $property->getAttributes(CastWith::class);

            if (!empty($castWithAttributes)) {
                $castWith = $castWithAttributes[0]->newInstance();
                $caster = new ($castWith->casterClass)(...$castWith->args);
                $value = $caster->cast($value);
            }

            $this->{$key} = $value;
        }

        // Default uninitialized nullable properties to null, throw for missing required ones
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isInitialized($this)) {
                continue;
            }

            $type = $property->getType();

            if ($type instanceof \ReflectionNamedType && $type->allowsNull()) {
                $property->setValue($this, null);

                continue;
            }

            throw new \InvalidArgumentException(sprintf(
                'Required property [%s] is missing on [%s].',
                $property->getName(),
                static::class
            ));
        }

        // Run validators on all initialized properties
        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isInitialized($this)) {
                continue;
            }

            $value = $property->getValue($this);

            foreach ($property->getAttributes() as $attribute) {
                $instance = $attribute->newInstance();

                if ($instance instanceof Validator) {
                    $result = $instance->validate($value);

                    if (!$result->isValid) {
                        throw new \InvalidArgumentException(sprintf(
                            'Validation failed for property [%s]: %s',
                            $property->getName(),
                            $result->message
                        ));
                    }
                }
            }
        }
    }

    /**
     * Create a copy of this DTO with selected properties overridden.
     */
    public function with(mixed ...$args): static
    {
        if (count($args) === 1 && array_key_first($args) === 0 && is_array($args[0])) {
            $args = $args[0];
        }

        $clone = clone $this;
        $class = new \ReflectionClass($clone);

        foreach ($args as $key => $value) {
            if (!$class->hasProperty($key)) {
                continue;
            }

            $property = $class->getProperty($key);
            $castWithAttributes = $property->getAttributes(CastWith::class);

            if (!empty($castWithAttributes)) {
                $castWith = $castWithAttributes[0]->newInstance();
                $caster = new ($castWith->casterClass)(...$castWith->args);
                $value = $caster->cast($value);
            } elseif (is_array($value)) {
                // Auto-cast arrays to typed DataTransferObject properties
                $type = $property->getType();

                if ($type instanceof \ReflectionNamedType && !$type->isBuiltin()) {
                    $typeName = $type->getName();

                    if (is_subclass_of($typeName, self::class)) {
                        $value = new $typeName($value);
                    }
                }
            }

            $clone->{$key} = $value;
        }

        return $clone;
    }

    public function toArray(): array
    {
        $result = [];
        $class = new \ReflectionClass($this);

        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isInitialized($this)) {
                continue;
            }

            $value = $property->getValue($this);

            if ($value instanceof self) {
                $value = $value->toArray();
            } elseif (is_array($value)) {
                $value = array_map(
                    fn (mixed $item) => $item instanceof self ? $item->toArray() : $item,
                    $value
                );
            }

            $result[$property->getName()] = $value;
        }

        return $result;
    }
}
