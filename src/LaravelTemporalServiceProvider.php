<?php

namespace Keepsuit\LaravelTemporal;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Str;
use Keepsuit\LaravelTemporal\Commands\ActivityMakeCommand;
use Keepsuit\LaravelTemporal\Commands\InstallCommand;
use Keepsuit\LaravelTemporal\Commands\InterceptorMakeCommand;
use Keepsuit\LaravelTemporal\Commands\TestServerCommand;
use Keepsuit\LaravelTemporal\Commands\WorkCommand;
use Keepsuit\LaravelTemporal\Commands\WorkflowMakeCommand;
use Keepsuit\LaravelTemporal\DataConverter\LaravelPayloadConverter;
use Keepsuit\LaravelTemporal\Support\DiscoverActivities;
use Keepsuit\LaravelTemporal\Support\DiscoverWorkflows;
use Keepsuit\LaravelTemporal\Support\ServerStateFile;
use Keepsuit\LaravelTemporal\Testing\TemporalMocker;
use Keepsuit\LaravelTemporal\Testing\TemporalMockerCache;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Temporal\Client\ClientOptions;
use Temporal\Client\GRPC\ServiceClient;
use Temporal\Client\GRPC\ServiceClientInterface;
use Temporal\Client\WorkflowClient;
use Temporal\Client\WorkflowClientInterface;
use Temporal\DataConverter\BinaryConverter;
use Temporal\DataConverter\DataConverter;
use Temporal\DataConverter\DataConverterInterface;
use Temporal\DataConverter\NullConverter;
use Temporal\DataConverter\ProtoJsonConverter;
use Temporal\Interceptor\SimplePipelineProvider;
use Temporal\Worker\WorkerOptions;

class LaravelTemporalServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-temporal')
            ->hasConfigFile()
            ->hasCommands([
                WorkCommand::class,
                InstallCommand::class,
                TestServerCommand::class,
                WorkflowMakeCommand::class,
                ActivityMakeCommand::class,
                InterceptorMakeCommand::class,
            ]);
    }

    public function registeringPackage(): void
    {
        $this->setupTestingEnvironment();

        $this->app->bind(Contracts\Temporal::class, Temporal::class);

        $this->app->scoped(TemporalRegistry::class, $this->initTemporalRegistry(...));

        $this->app->bind(ServerStateFile::class, fn (Application $app) => new ServerStateFile(
            $app['config']->get('temporal.state_file', storage_path('logs/temporal-worker-state.json'))
        ));

        $this->app->bind(ServiceClientInterface::class, fn (Application $app) => ServiceClient::create(config('temporal.address')));

        $this->app->bind(DataConverterInterface::class, fn (Application $app) => new DataConverter(
            new NullConverter(),
            new BinaryConverter(),
            new ProtoJsonConverter(),
            new LaravelPayloadConverter()
        ));

        $this->app->bind(WorkflowClientInterface::class, fn (Application $app) => WorkflowClient::create(
            serviceClient: $app->make(ServiceClientInterface::class),
            options: (new ClientOptions())->withNamespace(config('temporal.namespace')),
            converter: $app->make(DataConverterInterface::class),
            interceptorProvider: new SimplePipelineProvider(array_map(
                fn (string $className) => $app->make($className),
                config('temporal.interceptors', [])
            ))
        ));
    }

    protected function initTemporalRegistry(Application $app): TemporalRegistry
    {
        $workflowPaths = [
            $app->path('Workflows'),
            $app->path('Temporal/Workflows'),
        ];

        $activityPaths = [
            $this->app->path('Workflows'),
            $this->app->path('Activities'),
            $this->app->path('Temporal/Activities'),
            $this->app->path('Temporal/Workflows'),
        ];

        $registry = new TemporalRegistry();

        foreach ($workflowPaths as $workflowPath) {
            $registry->registerWorkflows(...DiscoverWorkflows::within($workflowPath));
        }

        $registry->registerWorkflows(...$app['config']->get('temporal.workflows', []));

        foreach ($activityPaths as $activityPath) {
            $registry->registerActivities(...DiscoverActivities::within($activityPath));
        }

        $registry->registerActivities(...$app['config']->get('temporal.activities', []));

        return $registry;
    }

    protected function setupTestingEnvironment(): void
    {
        if (! $this->app->environment('testing')) {
            return;
        }

        if (ParallelTesting::token() !== false) {
            config()->set('temporal.rpc_port', env('TEMPORAL_TESTING_RPC_PORT', 6001) + ParallelTesting::token());

            if (env('TEMPORAL_TESTING_SERVER', true)) {
                [$host, $port] = Str::of(config('temporal.address'))->explode(':', 2)->all();
                config()->set('temporal.address', sprintf('%s:%s', $host, (int) $port + (int) ParallelTesting::token()));
            } else {
                config()->set('temporal.namespace', sprintf('%s-%s', config('temporal.namespace'), ParallelTesting::token()));
            }
        }

        $this->app->singleton(TemporalMocker::class, fn (Application $app) => new TemporalMocker(
            cache: TemporalMockerCache::create()
        ));
    }
}
