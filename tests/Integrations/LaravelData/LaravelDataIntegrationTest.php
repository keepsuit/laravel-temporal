<?php

use Illuminate\Support\Collection;
use Keepsuit\LaravelTemporal\DataConverter\LaravelPayloadConverter;
use Keepsuit\LaravelTemporal\Integrations\LaravelData\LaravelDataHelpers;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\AdvancedDataItem;
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

    $input = new AdvancedDataItem(new TemporalSerializableItem(123));

    $payload = $converter->toPayload($input);

    $data = $converter->fromPayload($payload, new Type(AdvancedDataItem::class));

    expect($data)
        ->toBeInstanceOf(AdvancedDataItem::class)
        ->item->id->toEqual(123);
});

it('(v3) can deserialize Data values with data collection', function () {
    $converter = new LaravelPayloadConverter();

    $payload = $converter->toPayload(new DataItemV3(
        123,
        ['a' => 1, 'b' => 2],
        DataItemV3::collection([['id' => 4], ['id' => 5]])
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
})->skip(LaravelDataHelpers::version() !== 3);

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
