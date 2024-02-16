<?php

namespace Keepsuit\LaravelTemporal\Support;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Bootstrap\RegisterProviders;
use Illuminate\Foundation\Bootstrap\SetRequestForConsole;
use ReflectionObject;
use RuntimeException;

class ApplicationFactory
{
    public function __construct(protected string $basePath)
    {
    }

    /**
     * Create a new application instance.
     *
     * @param  array<string, mixed>  $initialInstances
     */
    public function createApplication(array $initialInstances = []): Application
    {
        $path = $this->basePath.'/bootstrap/app.php';

        if (! file_exists($path)) {
            throw new RuntimeException(sprintf('Application bootstrap file not found [%s].', $path));
        }

        return $this->warm($this->bootstrap(require $path, $initialInstances));
    }

    /**
     * Bootstrap the given application.
     *
     * @param  array<string, mixed>  $initialInstances
     */
    public function bootstrap(Application $app, array $initialInstances = []): Application
    {
        foreach ($initialInstances as $key => $value) {
            $app->instance($key, $value);
        }

        $app->bootstrapWith($this->getBootstrappers($app));

        $app->loadDeferredProviders();

        return $app;
    }

    /**
     * Get the application's HTTP kernel bootstrappers.
     *
     * @return mixed[]
     */
    protected function getBootstrappers(Application $app): array
    {
        $method = (new ReflectionObject(
            $kernel = $app->make(Kernel::class)
        ))->getMethod('bootstrappers');

        $method->setAccessible(true);

        return $this->injectBootstrapperBefore(
            RegisterProviders::class,
            SetRequestForConsole::class,
            $method->invoke($kernel)
        );
    }

    /**
     * Inject a given bootstrapper before another bootstrapper.
     *
     * @param  mixed[]  $bootstrappers
     * @return mixed[]
     */
    protected function injectBootstrapperBefore(string $before, string $inject, array $bootstrappers): array
    {
        $injectIndex = array_search($before, $bootstrappers, true);

        if (is_int($injectIndex)) {
            array_splice($bootstrappers, $injectIndex, 0, [$inject]);
        }

        return $bootstrappers;
    }

    /**
     * Warm the application with pre-resolved, cached services that persist across requests.
     *
     * @param  mixed[]  $services
     */
    public function warm(Application $app, array $services = []): Application
    {
        foreach ($services ?: $app->make('config')->get('temporal.warm', []) as $service) {
            if (! is_string($service)) {
                continue;
            }

            if (! $app->bound($service)) {
                continue;
            }

            $app->make($service);

            return $app;
        }

        return $app;
    }
}
