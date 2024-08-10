<?php

use Illuminate\Support\Collection;
use Keepsuit\LaravelTemporal\DataConverter\LaravelPayloadConverter;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\AdvancedDataItem;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\DataItem;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\TemporalSerializableItem;
use Temporal\DataConverter\Type;

it('can deserialize Data values', function () {
    $converter = new LaravelPayloadConverter;

    $payload = $converter->toPayload(new DataItem(123));

    $data = $converter->fromPayload($payload, new Type(DataItem::class));

    expect($data)
        ->toBeInstanceOf(DataItem::class)
        ->id->toBe(123);
});

it('can convert TemporalSerializable property with cast/transformer', function () {
    $converter = new LaravelPayloadConverter;

    $input = new AdvancedDataItem(new TemporalSerializableItem(123));

    $payload = $converter->toPayload($input);

    $data = $converter->fromPayload($payload, new Type(AdvancedDataItem::class));

    expect($data)
        ->toBeInstanceOf(AdvancedDataItem::class)
        ->item->id->toEqual(123);
});

it('can convert TemporalSerializable array property with cast/transformer', function () {
    $converter = new LaravelPayloadConverter;

    $input = new AdvancedDataItem(
        new TemporalSerializableItem(123),
        Collection::make([new TemporalSerializableItem(4), new TemporalSerializableItem(5)])
    );

    $payload = $converter->toPayload($input);

    $data = $converter->fromPayload($payload, new Type(AdvancedDataItem::class));

    expect($data)
        ->toBeInstanceOf(AdvancedDataItem::class)
        ->item->toBeInstanceOf(TemporalSerializableItem::class)
        ->item->id->toEqual(123)
        ->collection->toBeInstanceOf(Collection::class)
        ->collection->toHaveCount(2)
        ->collection->first()->toBeInstanceOf(TemporalSerializableItem::class)
        ->collection->first()->id->toEqual(4);
});

it('can deserialize Data values with collection', function () {
    $converter = new LaravelPayloadConverter;

    $payload = $converter->toPayload(new DataItem(
        123,
        ['a' => 1, 'b' => 2],
        new Collection([new DataItem(4), new DataItem(5)])
    ));

    $data = $converter->fromPayload($payload, new Type(DataItem::class));

    expect($data)
        ->toBeInstanceOf(DataItem::class)
        ->id->toBe(123)
        ->values->toEqual(['a' => 1, 'b' => 2])
        ->collection->toBeInstanceOf(Collection::class)
        ->collection->toHaveCount(2)
        ->collection->get(0)->toBeInstanceOf(DataItem::class)
        ->collection->get(0)->id->toBe(4);
});

it('can convert collection of TemporalSerializable', function () {
    $converter = new LaravelPayloadConverter;

    $input = new AdvancedDataItem(
        new TemporalSerializableItem(123),
        new Collection([new TemporalSerializableItem(4), new TemporalSerializableItem(5)])
    );

    $payload = $converter->toPayload($input);

    $data = $converter->fromPayload($payload, new Type(AdvancedDataItem::class));

    expect($data)
        ->toBeInstanceOf(AdvancedDataItem::class)
        ->item->toBeInstanceOf(TemporalSerializableItem::class)
        ->item->id->toEqual(123)
        ->collection->toBeInstanceOf(Collection::class)
        ->collection->toHaveCount(2)
        ->collection->get(0)->toBeInstanceOf(TemporalSerializableItem::class)
        ->collection->get(0)->id->toEqual(4);
});
