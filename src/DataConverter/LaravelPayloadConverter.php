<?php

namespace Keepsuit\LaravelTemporal\DataConverter;

use Illuminate\Database\Eloquent\Model;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use ReflectionClass;
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
            return $this->create(\Safe\json_encode($value->toTemporalPayload(), self::JSON_FLAGS));
        }

        if ($value instanceof \BackedEnum) {
            return $this->create(\Safe\json_encode($value->value, self::JSON_FLAGS));
        }

        if ($value instanceof Data) {
            return $this->create($value->toJson(self::JSON_FLAGS));
        }

        if ($value instanceof Model) {
            return $this->create($value->toJson(self::JSON_FLAGS));
        }

        return parent::toPayload($value);
    }

    public function fromPayload(Payload $payload, Type $type)
    {
        if (! $type->isClass()) {
            return parent::fromPayload($payload, $type);
        }

        $typeName = $type->getName();

        if (! class_exists($typeName)) {
            return parent::fromPayload($payload, $type);
        }

        try {
            $data = \Safe\json_decode($payload->getData(), true, 512, self::JSON_FLAGS);
        } catch (Throwable $throwable) {
            throw new DataConverterException($throwable->getMessage(), $throwable->getCode(), $throwable);
        }

        try {
            $reflection = new ReflectionClass($typeName);
            $class = $reflection->getName();

            if ($reflection->implementsInterface(TemporalSerializable::class)) {
                /** @var class-string<TemporalSerializable> $class */
                return $class::fromTemporalPayload($data);
            }

            if ($reflection->isEnum()) {
                /** @var class-string<\BackedEnum> $class */
                return $class::from($data);
            }

            if ($reflection->isSubclassOf(Data::class)) {
                /** @var class-string<Data> $class */
                return $class::from($data);
            }

            if ($reflection->isSubclassOf(Model::class)) {
                return $reflection->newInstance($data);
            }
        } catch (Throwable) {
        }

        return parent::fromPayload($payload, $type);
    }
}
