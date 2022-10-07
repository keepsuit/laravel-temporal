<?php

namespace Keepsuit\LaravelTemporal\Testing;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;

class TemporalTestingEnvironment
{
    protected TemporalTestingServer $temporalServer;

    protected TemporalTestingWorker $temporalWorker;

    public function __construct(
        protected bool $debug = false
    ) {
        $this->temporalServer = TemporalTestingServer::create();
        $this->temporalWorker = TemporalTestingWorker::create();
    }

    public static function create(bool $debug = false): self
    {
        return new self($debug);
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
        $temporalAddress = config('temporal.address', '127.0.0.1:7233');
        $temporalPort = parse_url((string) $temporalAddress, PHP_URL_PORT);

        $this->temporalServer->setDebugOutput($this->debug)->start($temporalPort);
    }

    public function startTemporalWorker(): void
    {
        $this->temporalWorker->setDebugOutput($this->debug)->start();
    }
}
