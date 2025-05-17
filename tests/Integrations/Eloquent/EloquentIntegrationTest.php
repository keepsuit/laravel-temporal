<?php

use Keepsuit\LaravelTemporal\DataConverter\LaravelPayloadConverter;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\SampleModel;
use Keepsuit\LaravelTemporal\Tests\Fixtures\Converter\SampleRelatedModel;

it('can serialize Eloquent model', function () {
    $model = new SampleModel(['id' => 123]);

    $converter = new LaravelPayloadConverter;

    $payload = $converter->toPayload($model);

    expect($payload->getData())->toBe(json_encode(['id' => 123], LaravelPayloadConverter::JSON_FLAGS));
});

it('can serialize Eloquent model with relations', function () {
    $model = new SampleModel(['id' => 123]);
    $model->setRelation('related', new \Illuminate\Database\Eloquent\Collection([new SampleRelatedModel(['id' => 456])]));

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

    $data = $converter->fromPayload($payload, new \Temporal\DataConverter\Type(SampleModel::class));

    expect($data)
        ->toBeInstanceOf(SampleModel::class)
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

    $data = $converter->fromPayload($payload, new \Temporal\DataConverter\Type(SampleModel::class));

    expect($data)
        ->toBeInstanceOf(SampleModel::class)
        ->id->toBe(123)
        ->relationLoaded('related')->toBeTrue()
        ->related->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class)
        ->related->first()->toBeInstanceOf(SampleRelatedModel::class)
        ->related->first()->id->toBe(456);
});
