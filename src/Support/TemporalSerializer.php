<?php

namespace Keepsuit\LaravelTemporal\Support;

use Illuminate\Support\Arr;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;

class TemporalSerializer
{
    public function serialize(mixed $value): mixed
    {
        if ($value instanceof TemporalSerializable) {
            return $value->toTemporalPayload();
        }

        if (is_array($value)) {
            return Arr::map($value, function ($data) {
                if ($data instanceof TemporalSerializable) {
                    return $data->toTemporalPayload();
                }

                return $data;
            });
        }

        return $value;
    }

    public function deserialize(mixed $value, string $type, bool $isArray = false): mixed
    {
        if ($value === null) {
            return null;
        }

        if (! class_exists($type)) {
            throw new \RuntimeException("Cannot deserialize value, class $type does not exist");
        }

        if (! in_array(TemporalSerializable::class, \Safe\class_implements($type))) {
            throw new \RuntimeException("Cannot deserialize value, class $type does not implement TemporalSerializable");
        }

        if ($value instanceof $type) {
            return $value;
        }

        if (! is_array($value)) {
            throw new \RuntimeException('Cannot deserialize value, expected array');
        }

        if ($isArray) {
            return Arr::map($value, function ($data) use ($type) {
                if ($data instanceof $type) {
                    return $data;
                }

                /** @var class-string<TemporalSerializable> $type */
                return $type::fromTemporalPayload($data);
            });
        }

        /** @var class-string<TemporalSerializable> $type */
        return $type::fromTemporalPayload($value);
    }
}
