<?php

use Temporal\Client\GRPC\OperatorClientInterface;
use Temporal\Client\ScheduleClientInterface;

it('resolves temporal clients from the container', function (string $abstract) {
    expect(app($abstract))->toBeInstanceOf($abstract);
})->with([
    ScheduleClientInterface::class,
    OperatorClientInterface::class,
]);
