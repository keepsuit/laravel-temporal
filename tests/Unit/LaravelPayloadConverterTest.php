<?php

use Illuminate\Database\Eloquent\Model;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Keepsuit\LaravelTemporal\DataConverter\LaravelPayloadConverter;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\BaseModel;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\EnumItem;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\TemporalSerializableItem;
use Temporal\DataConverter\Type;

it('can serialize values', function ($value, mixed $result) {
    $converter = new LaravelPayloadConverter;

    $payload = $converter->toPayload($value);

    expect($payload->getData())->toBe(json_encode($result, LaravelPayloadConverter::JSON_FLAGS));
})->with([
    TemporalSerializable::class => [new TemporalSerializableItem(123), ['type' => TemporalSerializable::class, 'id' => 123]],
    BackedEnum::class => [EnumItem::A, 'a'],
    Model::class => [new BaseModel(['id' => 123]), ['id' => 123]],
]);

it('can deserialize values', function ($input, $type) {
    $converter = new LaravelPayloadConverter;

    $payload = $converter->toPayload($input);

    $data = $converter->fromPayload($payload, $type);

    expect($data)->toEqual($input);
})->with([
    'native type' => [123, new Type(Type::TYPE_INT)],
    TemporalSerializable::class => [new TemporalSerializableItem(123), new Type(TemporalSerializableItem::class)],
    BackedEnum::class => [EnumItem::A, new Type(EnumItem::class)],
    Model::class => [new BaseModel(['id' => 123]), new Type(BaseModel::class)],
]);
