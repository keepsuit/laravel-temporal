<?php

namespace Keepsuit\LaravelTemporal\DataConverter;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use ReflectionClass;
use ReflectionException;
use Spatie\LaravelData\Data;
use Temporal\Api\Common\V1\Payload;
use Temporal\DataConverter\JsonConverter;
use Temporal\DataConverter\Type;
use Temporal\Exception\DataConverterException;
use Throwable;

class LaravelPayloadConverter extends JsonConverter
{
    public function toPayload($value): ?Payload
    {
        if ($value instanceof TemporalSerializable) {
            return $this->create(json_encode($value->toTemporalPayload(), self::JSON_FLAGS));
        }

        if ($value instanceof Jsonable) {
            return $this->create($value->toJson(self::JSON_FLAGS));
        }

        if ($value instanceof JsonSerializable) {
            return $this->create(json_encode($value->jsonSerialize(), self::JSON_FLAGS));
        }

        if ($value instanceof Arrayable) {
            return $this->create(json_encode($value->toArray(), self::JSON_FLAGS));
        }

        return parent::toPayload($value);
    }

    public function fromPayload(Payload $payload, Type $type)
    {
        try {
            $data = json_decode($payload->getData(), true, 512, self::JSON_FLAGS);
        } catch (Throwable $throwable) {
            throw new DataConverterException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }

        if (! $type->isClass()) {
            return parent::fromPayload($payload, $type);
        }

        try {
            $reflection = new ReflectionClass($type->getName());

            if ($reflection->implementsInterface(TemporalSerializable::class)) {
                /** @var class-string<TemporalSerializable> $class */
                $class = $reflection->getName();

                return $class::fromTemporalPayload($data);
            }

            if ($reflection->isSubclassOf(Data::class)) {
                /** @var class-string<Data> $class */
                $class = $reflection->getName();

                return $class::from($data);
            }

            return $reflection->newInstance($data);
        } catch (ReflectionException) {
        }

        return parent::fromPayload($payload, $type);
    }
}
