<?php

namespace MailCarrier\Dto;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use MailCarrier\Models\Casts\DataTransferObject as DataTransferObjectCast;

abstract class CastableDataTransferObject extends DataTransferObject implements Arrayable, Castable, Jsonable
{
    public static function castUsing(array $arguments)
    {
        return new DataTransferObjectCast(static::class, $arguments);
    }

    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    public static function fromJson(string $json, int $options = 0)
    {
        // @phpstan-ignore-next-line
        return new static(json_decode(
            $json,
            true, // assoc
            512, // depth
            $options // flags
        ));
    }
}
