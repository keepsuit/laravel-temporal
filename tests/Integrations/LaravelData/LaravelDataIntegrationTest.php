<?php

use Keepsuit\LaravelTemporal\DataConverter\LaravelPayloadConverter;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\AdvancedDataItem;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\DataItem;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\TemporalSerializableItem;
use Temporal\DataConverter\Type;

it('can deserialize Data values', function () {
    $converter = new LaravelPayloadConverter();

    $payload = $converter->toPayload(new DataItem(123));

    $data = $converter->fromPayload($payload, new Type(DataItem::class));

    expect($data)->toEqual(new DataItem(123));
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
