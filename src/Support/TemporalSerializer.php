<?php

namespace Keepsuit\LaravelTemporal\Support;

use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Keepsuit\LaravelTemporal\Exceptions\TemporalSerializerException;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\TemporalSerializableItem;

class TemporalSerializer
{
    public function serialize(TemporalSerializable $value): array
    {
        return $value->toTemporalPayload();
    }

    /**
     * @param  class-string<TemporalSerializable>  $className
     *
     * @throw TemporalSerializerException
     */
    public function deserialize(?array $value, string $className): ?TemporalSerializableItem
    {
        if (! class_exists($className)) {
            throw TemporalSerializerException::targetClassDoesntExists($className);
        }

        if (! in_array(TemporalSerializable::class, rescue(fn () => \Safe\class_implements($className), []))) {
            throw TemporalSerializerException::targetClassIsNotSerializable($className);
        }

        if ($value === null) {
            return null;
        }

        /** @var class-string<TemporalSerializable> $className */
        return $className::fromTemporalPayload($value);
    }
}
