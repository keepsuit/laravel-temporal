<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

class TemporalTestingEnvironment
{
    protected TemporalServer $temporalServer;

    protected TemporalTestingWorker $temporalWorker;

    protected bool $debug = false;

    public function __construct(
        protected bool $timeSkipping = false,
    ) {
        $this->temporalServer = match ($this->timeSkipping) {
            true => TimeSkippingTemporalServer::create(),
            default => LocalTemporalServer::create(),
        };
        $this->temporalWorker = TemporalTestingWorker::create();
    }

    public static function create(bool $timeSkipping = false): self
    {
        return new self($timeSkipping);
    }

    public static function bootstrap(Application $app): void
    {
        $app->make(Kernel::class)->bootstrap();

        $env = static::create();

        $env->start(! env('TEMPORAL_TESTING_SERVER', true));

        register_shutdown_function(fn () => $env->stop());
    }

    public function setDebugOutput(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function start(bool $onlyWorker = false): void
    {
        if (! $onlyWorker) {
            $this->startTemporalServer();
        }

        $this->startTemporalWorker();
    }

    public function stop(): void
    {
        $this->temporalWorker->stop();
        $this->temporalServer->stop();
    }

    public function startTemporalServer(): void
    {
        $this->temporalServer->setDebugOutput($this->debug)->start();
    }

    public function startTemporalWorker(): void
    {
        $this->temporalWorker->setDebugOutput($this->debug)->start();
    }
}
