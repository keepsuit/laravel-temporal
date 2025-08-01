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
use Temporal\DataConverter\ProtoConverter;
use Temporal\DataConverter\ProtoJsonConverter;
use Temporal\Interceptor\SimplePipelineProvider;

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

    public function packageRegistered(): void
    {
        $this->setupTestingEnvironment();

        $this->app->bind(Contracts\Temporal::class, Temporal::class);

        $this->app->scoped(TemporalRegistry::class, $this->initTemporalRegistry(...));

        $this->app->bind(ServerStateFile::class, fn (Application $app) => new ServerStateFile(
            $app['config']->get('temporal.state_file', storage_path('logs/temporal-worker-state.json'))
        ));

        $this->app->scoped(ServiceClientInterface::class, function (Application $app): ServiceClientInterface {
            $address = config('temporal.address');

            $clientKey = config('temporal.tls.client_key');
            $clientCert = config('temporal.tls.client_cert');
            $rootCa = config('temporal.tls.root_ca');
            $serverName = config('temporal.tls.server_name');

            if (is_string($clientKey) && $clientKey !== '' && is_string($clientCert) && $clientCert !== '') {
                return ServiceClient::createSSL(
                    $address,
                    $rootCa,
                    $clientKey,
                    $clientCert,
                    $serverName
                );
            } else {
                return ServiceClient::create($address);
            }
        });

        $this->app->bind(DataConverterInterface::class, fn (Application $app) => new DataConverter(
            new NullConverter,
            new BinaryConverter,
            new ProtoJsonConverter,
            new ProtoConverter,
            new LaravelPayloadConverter
        ));

        $this->app->scoped(WorkflowClientInterface::class, fn (Application $app) => WorkflowClient::create(
            serviceClient: $app->make(ServiceClientInterface::class),
            options: (new ClientOptions)->withNamespace(config('temporal.namespace')),
            converter: $app->make(DataConverterInterface::class),
            interceptorProvider: new SimplePipelineProvider(array_map(
                fn (string $className) => $app->make($className),
                config('temporal.interceptors', [])
            ))
        ));
    }

    protected function initTemporalRegistry(Application $app): TemporalRegistry
    {
        $registry = new TemporalRegistry;

        $registry->registerWorkflows(...DiscoverWorkflows::within($app->path()));
        $registry->registerActivities(...DiscoverActivities::within($app->path()));

        return $registry;
    }

    protected function setupTestingEnvironment(): void
    {
        if (! $this->app->environment('testing')) {
            return;
        }

        if (ParallelTesting::token() !== false) {
            $rpcPort = (int) config('temporal.rpc_port', 6001);
            config()->set('temporal.rpc_port', $rpcPort + (int) ParallelTesting::token());

            if (config('temporal.testing.server', true)) {
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
