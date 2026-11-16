<?php

use Keepsuit\LaravelTemporal\Support\RoadRunnerBinaryHelper;

it('detects the config version from the RoadRunner binary version', function (string $binaryVersion, string $configVersion) {
    expect((new RoadRunnerBinaryHelper)->configVersionFor($binaryVersion))->toBe($configVersion);
})->with([
    'RoadRunner 2.x' => ['2.12.3', '2.7'],
    'RoadRunner 2023.x' => ['2023.3.12', '3'],
    'RoadRunner 2025.x' => ['2025.1.15', '3'],
    'RoadRunner 3.0' => ['3.0.0', '3'],
    'RoadRunner 3.x' => ['3.0.1', '3'],
]);

it('rejects RoadRunner versions older than 2.x', function () {
    (new RoadRunnerBinaryHelper)->configVersionFor('1.9.2');
})->throws(RuntimeException::class);
