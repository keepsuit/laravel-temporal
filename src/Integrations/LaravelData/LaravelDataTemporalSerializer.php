<?php

namespace Keepsuit\LaravelTemporal\Integrations\LaravelData;

use Illuminate\Support\Arr;
use Illuminate\Support\Enumerable;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Keepsuit\LaravelTemporal\Exceptions\TemporalSerializerException;
use Spatie\LaravelData\Casts\Uncastable;
use Spatie\LaravelData\Support\DataProperty;

class LaravelDataTemporalSerializer
{
    public function transform(mixed $value): array
    {
        if ($value instanceof Enumerable) {
            return $value->map(fn (mixed $item) => $this->serializeItem($item))->toArray();
        }

        if (is_array($value)) {
            return Arr::map($value, fn (mixed $item) => $this->serializeItem($item));
        }

        return $this->serializeItem($value);
    }

    public function cast(DataProperty $property, mixed $value, ?string $type = null): mixed
    {
        $enumerableClass = $property->type->findAcceptedTypeForBaseType(\Illuminate\Support\Enumerable::class);
        $isArrayOrCollection = $enumerableClass !== null || $property->type->acceptsType('array');

        if ($isArrayOrCollection && $type === null) {
            return Uncastable::create();
        }

        if ($isArrayOrCollection) {
            $items = Arr::map($value, fn ($item) => rescue(
                fn () => $this->deserializeItem($item, $type),
                fn () => Uncastable::create(),
                report: false
            ));

            return $enumerableClass !== null ? $enumerableClass::make($items) : $items;
        }

        $type = $this->type ?? $property->type->findAcceptedTypeForBaseType(TemporalSerializable::class);

        return rescue(
            fn () => $this->deserializeItem($value, $type),
            fn () => Uncastable::create(),
            report: false
        );
    }

    protected function serializeItem(mixed $item): mixed
    {
        if ($item instanceof TemporalSerializable) {
            return $item->toTemporalPayload();
        }

        return $item;
    }

    /**
     * @throws TemporalSerializerException
     */
    protected function deserializeItem(?array $value, string $className): ?TemporalSerializable
    {
        if (! class_exists($className)) {
            throw TemporalSerializerException::targetClassDoesntExists($className);
        }

        if (! in_array(TemporalSerializable::class, rescue(fn () => \Safe\class_implements($className), [], false))) {
            throw TemporalSerializerException::targetClassIsNotSerializable($className);
        }

        if ($value === null) {
            return null;
        }

        /** @var class-string<TemporalSerializable> $className */
        return $className::fromTemporalPayload($value);
    }
}
