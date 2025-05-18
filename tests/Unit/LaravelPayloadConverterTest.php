<?php

use Illuminate\Database\Eloquent\Collection;
use Keepsuit\LaravelTemporal\Contracts\TemporalSerializable;
use Keepsuit\LaravelTemporal\DataConverter\LaravelPayloadConverter;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\BaseModel;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\BaseRelatedModel;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\EnumItem;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\TemporalSerializableItem;
use Temporal\DataConverter\Type;

it('can serialize values', function ($value, mixed $result) {
    $converter = new LaravelPayloadConverter;

    $payload = $converter->toPayload($value);

    expect($payload->getData())->toBe(json_encode($result, LaravelPayloadConverter::JSON_FLAGS));
})->with([
    'temporal serializable' => fn () => [
        new TemporalSerializableItem(123),
        ['type' => TemporalSerializable::class, 'id' => 123],
    ],
    'enum' => fn () => [EnumItem::A, 'a'],
    'model' => fn () => [
        (new BaseModel)->newInstance(['id' => 123]),
        ['id' => 123],
    ],
    'model with relations' => fn () => [
        (new BaseModel)
            ->newInstance(['id' => 123])
            ->setRelation('related', Collection::make([(new BaseRelatedModel)->newInstance(['id' => 456])])),
        ['id' => 123, 'related' => [['id' => 456]]],
    ],
]);

it('can deserialize values', function ($input, $type) {
    $converter = new LaravelPayloadConverter;

    $payload = $converter->toPayload($input);

    $data = $converter->fromPayload($payload, $type);

    expect($data)->toEqual($input);
})->with([
    'native type' => fn () => [123, new Type(Type::TYPE_INT)],
    'temporal serializable' => fn () => [
        new TemporalSerializableItem(123),
        new Type(TemporalSerializableItem::class),
    ],
    'enum' => fn () => [EnumItem::A, new Type(EnumItem::class)],
    'model' => fn () => [
        (new BaseModel)->newInstance(['id' => 123], true),
        new Type(BaseModel::class),
    ],
    'model with relations' => fn () => [
        (new BaseModel)
            ->newInstance(['id' => 123], true)
            ->setRelation('related', Collection::make([(new BaseRelatedModel)->newInstance(['id' => 456], true)])),
        new Type(BaseModel::class),
    ],
]);
