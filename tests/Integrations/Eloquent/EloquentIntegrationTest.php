<?php

use Keepsuit\LaravelTemporal\DataConverter\LaravelPayloadConverter;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\TemporalSerializableModel;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\TemporalSerializableRelatedModel;

it('can serialize Eloquent model', function () {
    $model = new TemporalSerializableModel(['id' => 123]);

    $converter = new LaravelPayloadConverter;

    $payload = $converter->toPayload($model);

    expect($payload->getData())->toBe(json_encode(['id' => 123], LaravelPayloadConverter::JSON_FLAGS));
});

it('can serialize Eloquent model with relations', function () {
    $model = new TemporalSerializableModel(['id' => 123]);
    $model->setRelation('related', new \Illuminate\Database\Eloquent\Collection([new TemporalSerializableRelatedModel(['id' => 456])]));

    $converter = new LaravelPayloadConverter;

    $payload = $converter->toPayload($model);

    expect($payload->getData())->toBe(json_encode([
        'id' => 123,
        'related' => [
            ['id' => 456],
        ],
    ], LaravelPayloadConverter::JSON_FLAGS));
});

it('can unserialize Eloquent model', function () {
    $converter = new LaravelPayloadConverter;

    $payload = (new \Temporal\Api\Common\V1\Payload)
        ->setData(json_encode(['id' => 123], LaravelPayloadConverter::JSON_FLAGS));

    $data = $converter->fromPayload($payload, new \Temporal\DataConverter\Type(TemporalSerializableModel::class));

    expect($data)
        ->toBeInstanceOf(TemporalSerializableModel::class)
        ->id->toBe(123);
});

it('can unserialize Eloquent model with relations', function () {
    $converter = new LaravelPayloadConverter;

    $payload = (new \Temporal\Api\Common\V1\Payload)
        ->setData(json_encode([
            'id' => 123,
            'related' => [
                ['id' => 456],
            ],
        ], LaravelPayloadConverter::JSON_FLAGS));

    $data = $converter->fromPayload($payload, new \Temporal\DataConverter\Type(TemporalSerializableModel::class));

    expect($data)
        ->toBeInstanceOf(TemporalSerializableModel::class)
        ->id->toBe(123)
        ->relationLoaded('related')->toBeTrue()
        ->related->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class)
        ->related->first()->toBeInstanceOf(TemporalSerializableRelatedModel::class)
        ->related->first()->id->toBe(456);
});
