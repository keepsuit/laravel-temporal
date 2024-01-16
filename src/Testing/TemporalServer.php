<?php

namespace Keepsuit\LaravelTemporal\Testing;

interface TemporalServer
{
    public static function create(): static;

    public function setDebugOutput(bool $debug): static;

    public function start(?int $port = null): void;

    public function isRunning(): bool;

    public function stop(): void;
}
