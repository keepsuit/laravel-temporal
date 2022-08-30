<?php

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Keepsuit\LaravelTemporal\DataConverter\LaravelPayloadConverter;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\ArrayableItem;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\BaseClass;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\JsonableItem;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\JsonSerializableItem;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\TemporalSerializableItem;
use Temporal\DataConverter\Type;

it('can serialize values', function ($value, array $result) {
    $converter = new LaravelPayloadConverter();

    $payload = $converter->toPayload($value);

    expect($payload->getData())->toBe(json_encode($result, LaravelPayloadConverter::JSON_FLAGS));
})->with([
    Arrayable::class => [new ArrayableItem(123), ['type' => Arrayable::class, 'id' => 123]],
    Jsonable::class => [new JsonableItem(123), ['type' => Jsonable::class, 'id' => 123]],
    JsonSerializable::class => [new JsonSerializableItem(123), ['type' => JsonSerializable::class, 'id' => 123]],
    TemporalSerializable::class => [new TemporalSerializableItem(123), ['type' => TemporalSerializable::class, 'id' => 123]],
]);

it('can deserialize values', function ($input, $type) {
    $converter = new LaravelPayloadConverter();

    $payload = $converter->toPayload($input);

    $data = $converter->fromPayload($payload, $type);

    expect($data)->toEqual($input);
})->with([
    'native type' => [123, new Type(Type::TYPE_INT)],
    'base class' => [new BaseClass(['id' => 123]), new Type(BaseClass::class)],
    TemporalSerializable::class => [new TemporalSerializableItem(123), new Type(TemporalSerializableItem::class)],
]);
