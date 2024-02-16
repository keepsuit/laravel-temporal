<?php

use Illuminate\Support\Collection;
use Keepsuit\LaravelTemporal\DataConverter\LaravelPayloadConverter;
use Keepsuit\LaravelTemporal\Integrations\LaravelData\LaravelDataHelpers;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\AdvancedDataItemV3;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\AdvancedDataItemV4;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\DataItemV3;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\DataItemV4;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\TemporalSerializableItem;
use Temporal\DataConverter\Type;

it('can deserialize Data values', function () {
    $converter = new LaravelPayloadConverter();

    $payload = $converter->toPayload(new DataItemV3(123));

    $data = $converter->fromPayload($payload, new Type(DataItemV3::class));

    expect($data)
        ->toBeInstanceOf(DataItemV3::class)
        ->id->toBe(123);
});

it('can convert TemporalSerializable property with cast/transformer', function () {
    $converter = new LaravelPayloadConverter();

    $input = new AdvancedDataItemV3(new TemporalSerializableItem(123));

    $payload = $converter->toPayload($input);

    $data = $converter->fromPayload($payload, new Type(AdvancedDataItemV3::class));

    expect($data)
        ->toBeInstanceOf(AdvancedDataItemV3::class)
        ->item->id->toEqual(123);
});

it('can convert TemporalSerializable array property with cast/transformer', function () {
    $converter = new LaravelPayloadConverter();

    $input = new AdvancedDataItemV3(
        new TemporalSerializableItem(123),
        [new TemporalSerializableItem(4), new TemporalSerializableItem(5)]
    );

    $payload = $converter->toPayload($input);

    $data = $converter->fromPayload($payload, new Type(AdvancedDataItemV3::class));

    expect($data)
        ->toBeInstanceOf(AdvancedDataItemV3::class)
        ->item->toBeInstanceOf(TemporalSerializableItem::class)
        ->item->id->toEqual(123)
        ->collection->toBeArray()
        ->collection->toHaveCount(2)
        ->collection->{0}->toBeInstanceOf(TemporalSerializableItem::class)
        ->collection->{0}->id->toEqual(4);
});

it('(v3) can deserialize Data values with data collection', function () {
    $converter = new LaravelPayloadConverter();

    $payload = $converter->toPayload(new DataItemV3(
        123,
        ['a' => 1, 'b' => 2],
        method_exists(DataItemV3::class, 'collection')
            ? DataItemV3::collection([['id' => 4], ['id' => 5]])
            : DataItemV3::collect([['id' => 4], ['id' => 5]], \Spatie\LaravelData\DataCollection::class)
    ));

    $data = $converter->fromPayload($payload, new Type(DataItemV3::class));

    expect($data)
        ->toBeInstanceOf(DataItemV3::class)
        ->id->toBe(123)
        ->values->toEqual(['a' => 1, 'b' => 2])
        ->collection->toBeInstanceOf(\Spatie\LaravelData\DataCollection::class)
        ->collection->toHaveCount(2)
        ->collection->toCollection()->get(0)->toBeInstanceOf(DataItemV3::class)
        ->collection->toCollection()->get(0)->id->toBe(4);
});

it('(v4) can deserialize Data values with collection', function () {
    $converter = new LaravelPayloadConverter();

    $payload = $converter->toPayload(new DataItemV4(
        123,
        ['a' => 1, 'b' => 2],
        new Collection([new DataItemV4(4), new DataItemV4(5)])
    ));

    $data = $converter->fromPayload($payload, new Type(DataItemV4::class));

    expect($data)
        ->toBeInstanceOf(DataItemV4::class)
        ->id->toBe(123)
        ->values->toEqual(['a' => 1, 'b' => 2])
        ->collection->toBeInstanceOf(Collection::class)
        ->collection->toHaveCount(2)
        ->collection->get(0)->toBeInstanceOf(DataItemV4::class)
        ->collection->get(0)->id->toBe(4);
})->skip(LaravelDataHelpers::version() !== 4);

it('(v4) can convert collection of TemporalSerializable', function () {
    $converter = new LaravelPayloadConverter();

    $input = new AdvancedDataItemV4(
        new TemporalSerializableItem(123),
        new Collection([new TemporalSerializableItem(4), new TemporalSerializableItem(5)])
    );

    $payload = $converter->toPayload($input);

    $data = $converter->fromPayload($payload, new Type(AdvancedDataItemV4::class));

    expect($data)
        ->toBeInstanceOf(AdvancedDataItemV4::class)
        ->item->toBeInstanceOf(TemporalSerializableItem::class)
        ->item->id->toEqual(123)
        ->collection->toBeInstanceOf(Collection::class)
        ->collection->toHaveCount(2)
        ->collection->get(0)->toBeInstanceOf(TemporalSerializableItem::class)
        ->collection->get(0)->id->toEqual(4);
})->skip(LaravelDataHelpers::version() !== 4);
