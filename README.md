# Laravel temporal.io

[![Latest Version on Packagist](https://img.shields.io/packagist/v/keepsuit/laravel-temporal.svg?style=flat-square)](https://packagist.org/packages/keepsuit/laravel-temporal)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/keepsuit/laravel-temporal/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/keepsuit/laravel-temporal/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/keepsuit/laravel-temporal/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/keepsuit/laravel-temporal/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/keepsuit/laravel-temporal.svg?style=flat-square)](https://packagist.org/packages/keepsuit/laravel-temporal)

This package allow an easy integration of a Laravel app with a [temporal.io](https://temporal.io/),
which is _a distributed, scalable, durable, and highly available orchestration engine for asynchronous long-running business logic in a microservice
architecture_.

This package provides:

- Commands to create a new workflow, activity and interceptor
- Command to start the worker which will execute workflows and activities from the provided task queue
- Command to start a temporal dev server
- Testing helpers that allows mock of workflows and activities executions

## Installation

You can install the package via composer:

```bash
composer require keepsuit/laravel-temporal
```

Then download the latest `roadrunner` executable for your platform:

```bash
php artisan temporal:install
```

or

```bash
./vendor/bin/rr get-binary
```

> [!NOTE]
> You should run this command after every update to ensure that you have the latest version of `roadrunner` executable.

You can publish the config file with:

```bash
php artisan vendor:publish --tag="temporal-config"
```

This is the contents of the published config file:

```php
<?php

return [
    /**
     * Temporal server address
     */
    'address' => env('TEMPORAL_ADDRESS', 'localhost:7233'),

    /**
     * TLS configuration (optional)
     * Allows to configure the client to use a secure connection to the server.
     */
    'tls' => [
        /**
         * Path to the client key file (/path/to/client.key)
         */
        'client_key' => env('TEMPORAL_TLS_CLIENT_KEY'),
        /**
         * Path to the client cert file (/path/to/client.pem)
         */
        'client_cert' => env('TEMPORAL_TLS_CLIENT_CERT'),
        /**
         * Path to the root CA certificate file (/path/to/ca.cert)
         */
        'root_ca' => env('TEMPORAL_TLS_ROOT_CA'),
        /**
         * Override server name (default is hostname) to verify against the server certificate
         */
        'server_name' => env('TEMPORAL_TLS_SERVER_NAME'),
    ],

    /**
     * Temporal namespace
     */
    'namespace' => env('TEMPORAL_NAMESPACE', \Temporal\Client\ClientOptions::DEFAULT_NAMESPACE),

    /**
     * Default task queue
     */
    'queue' => \Temporal\WorkerFactory::DEFAULT_TASK_QUEUE,

    /**
     * Default retry policy
     */
    'retry' => [

        /**
         * Default retry policy for workflows
         */
        'workflow' => [
            /**
             * Initial retry interval (in seconds)
             * Default: 1
             */
            'initial_interval' => null,

            /**
             * Retry interval increment
             * Default: 2.0
             */
            'backoff_coefficient' => null,

            /**
             * Maximum interval before fail
             * Default: 100 x initial_interval
             */
            'maximum_interval' => null,

            /**
             * Maximum attempts
             * Default: unlimited
             */
            'maximum_attempts' => null,
        ],

        /**
         * Default retry policy for activities
         */
        'activity' => [
            /**
             * Initial retry interval (in seconds)
             * Default: 1
             */
            'initial_interval' => null,

            /**
             * Retry interval increment
             * Default: 2.0
             */
            'backoff_coefficient' => null,

            /**
             * Maximum interval before fail
             * Default: 100 x initial_interval
             */
            'maximum_interval' => null,

            /**
             * Maximum attempts
             * Default: unlimited
             */
            'maximum_attempts' => null,
        ],
    ],

    /**
     * Interceptors (middlewares) registered in the worker
     */
    'interceptors' => [
    ],

    /**
     * Directories to watch when server is started with `--watch` flag
     */
    'watch' => [
        'app',
        'config',
    ],

    /**
     * Integrations options
     */
    'integrations' => [

        /**
         * Eloquent models serialization/deserialization options
         */
        'eloquent' => [
            /**
             * Default attribute key case conversion when serialize a model before sending to temporal.
             * Supported values: 'snake', 'camel', null.
             */
            'serialize_attribute_case' => null,

            /**
             * Default attribute key case conversion when deserializing payload received from temporal.
             * Supported values: 'snake', 'camel', null.
             */
            'deserialize_attribute_case' => null,

            /**
             * If true adds additional metadata fields (`__exists`, `__dirty`) to the serialized model to improve deserialization.
             * `__exists`: indicate that the model is saved to database.
             * `__dirty`: indicate that the model has unsaved changes. (original values are not included in the serialized payload but the deserialized model will be marked as dirty)
             */
            'include_metadata_field' => false,
        ],
    ],
];
```

## Usage

Here we will see the utilities provided by this package.
For more information about Temporal and Workflow/Activity options please refer to
the [official documentation](https://docs.temporal.io/application-development/?lang=php).

### Create workflows and activities

To create a new workflow, you can use the `temporal:make:workflow {name}` command, which will create a new workflow interface & relative class in
the `app/Temporal/Workflows` directory.

To create a new activity, you can use the `temporal:make:activity {name}` command, which will create a new activity interface & relative class in
the `app/Temporal/Activities` directory.

> [!NOTE]
> If you already have workflow/activities in `app/Workflows` and `app/Activities` directories,
> the make commands will create the new workflow/activity in the these directories.

Workflows and activities inside `app` are automatically registered.
If you need to register workflows and activities from other paths (ex. from a package), you can register them manually with `TemporalRegistry` in your service provider.

```php
class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Register the workflows and activities when TemporalRegistry is resolved
        $this->callAfterResolving(\Keepsuit\LaravelTemporal\TemporalRegistry::class, function (\Keepsuit\LaravelTemporal\TemporalRegistry $registry) {
            $registry->registerWorkflows(YourWorkflowInterface::class);
            $registry->registerActivities(YourActivityInterface::class);
                
            // or with discovery
            $registry->registerWorkflows(...DiscoverWorkflows::within('/some/custom/path'));
            $registry->registerActivities(...DiscoverActivities::within('/some/custom/path'));
        });
    }
}
```

### Build and start a workflow

To start a workflow, you must build a stub through the `Temporal` Facade.

```php
$workflow = Temporal::newWorkflow()
    ->withTaskQueue('custom-task-queue') // Workflow options can be provided with fluent methods
    ->build(YourWorkflowInterface::class);

// This will start a new workflow execution and wait for the result
$result = $workflow->yourMethod();

// This will start a new workflow execution and return immediately
Temporal::workflowClient()->start($workflow);
```

### Build and start an activity

To start an activity, you must build a stub through the `Temporal` Facade (note that activities must be built inside a workflow).
Activity methods returns a Generator, so you must use the `yield` keyword to wait for the result.

```php
$activity = Temporal::newActivity()
    ->withTaskQueue('custom-task-queue') // Activity options can be provided with fluent methods
    ->build(YourActivityInterface::class);

$result = yield $activity->yourActivityMethod();
```

### Build and start a child workflow

Child workflows works like activity and like activities must be built inside a workflow.

```php
$childWorkflow = Temporal::newChildWorkflow()
    ->build(YourChildWorkflowInterface::class);

$result = yield $childWorkflow->yourActivityMethod();
```

### Input and output payloads

Payloads provided to workflows/activities as params and returned from them must be serialized, sent to the Temporal server and deserialized by the worker.
Activities can be executed by workers written in different languages, so the payload must be serialized in a common format.
Out of the box temporal sdk supports native php types and [protobuf](https://developers.google.com/protocol-buffers) messages.
This package adds some laravel specific options for serialization/deserialization of objects:

- `TemporalSerializable` interface can be implemented to add support for custom serialization/deserialization.
- Eloquent models can be correctly serialized/deserialized (with relations) adding `TemporalSerializable` interface and `TemporalEloquentSerialize` trait.
- [spatie/laravel-data](https://github.com/spatie/laravel-data) data objects are supported out of the box.

#### Spatie/Laravel-Data support

`spatie/laravel-data` is a package that provides a simple way to work with data objects in Laravel.
In order to take full advantage of `laravel-data`, it is suggested to use `v4.3.0` or higher.

> [!NOTE]
> The provided `TemporalSerializableCastAndTransformer` is compatible only with `laravel-data` `v4.3` or higher,
> if you are using an older version you can create your cast/transform.

Changes to be made in `config/data.php`:

```php
    // Enable iterables cast/transform
    'features' => [
        'cast_and_transform_iterables' => true,
    ],

    // Add support for TemporalSerializable transform
    'transformers' => [
        //...
        \Keepsuit\LaravelTemporal\Contracts\TemporalSerializable::class => \Keepsuit\LaravelTemporal\Integrations\LaravelData\TemporalSerializableCastAndTransformer::class,
    ],

    // Add support for TemporalSerializable cast
    'casts' => [
        //...
        \Keepsuit\LaravelTemporal\Contracts\TemporalSerializable::class => \Keepsuit\LaravelTemporal\Integrations\LaravelData\TemporalSerializableCastAndTransformer::class,
    ],
```

### Interceptors

Temporal interceptors are similar to laravel middleware and can be used to modify inbound and outbound SDK calls.
Interceptors can be registered in the `interceptors` config key.
See [temporal sdk v2.7](https://github.com/temporalio/sdk-php/releases/tag/v2.7.0) release notes for more information.
To create a new interceptor, you can use the `temporal:make:interceptor {name}` command, which will create a new interceptor class in the `app/Temporal/Interceptors` directory.

### Run the temporal worker

To run the temporal worker, you can use the `temporal:work {queue?}` command.

If you want to customize the options of the temporal worker, you can call `Temporal::buildWorkerOptionsUsing` in your service provider:

```php
class AppServiceProvider extends ServiceProvider
{
    public function boot(): vodi {
        \Keepsuit\LaravelTemporal\Facade\Temporal::buildWorkerOptionsUsing(function (string $taskQueue) {
            // you can build different worker options based on the task queue
            return \Temporal\Worker\WorkerOptions::new()
                ->withMaxConcurrentActivityTaskPollers(10)
                ->withMaxConcurrentWorkflowTaskPollers(10);
        });
    }
}
```

## Testing utilities

In order to test workflows end-to-end, you need a temporal server running.
This package provides two options to run a temporal server for testing purposes:

- Run `temporal:server` command, which will start a temporal testing server and use the `WithTemporalWorker` trait which will start a test worker
- Use the `WithTemporal` trait, which will start a temporal testing server and the test worker when running test and stop it on finish

> When using `WithTemporal` trait, you can set `TEMPORAL_TESTING_SERVER` env variable to `false`
> to disable the testing server and run only the worker.

### Time skipping

The default temporal server implementation is the dev server included in the temporal cli and this doesn't support time skipping.
In order to enable time skipping, you must:

- Run the `temporal:server` command with the `--enable-time-skipping` flag.
- Set `TEMPORAL_TESTING_SERVER_TIME_SKIPPING` env variable to `true` when using `WithTemporal` trait.

### Mocking workflows

Mocking a workflow can be useful when the workflow should be executed in another service or simply when you want to test other parts of your code
without running the workflow.
This works for child workflows too.

```php
Temporal::fake();

$workflowMock = Temporal::mockWorkflow(YourWorkflowInterface::class)
    ->onTaskQueue('custom-queue'); // not required but useful for mocking and asserting that workflow is executed on the correct queue
    ->andReturn('result');

// Your test code...

$workflowMock->assertDispatched();
$workflowMock->assertDispatchedTimes(1);
$workflowMock->assertNotDispatched();

// All assertion method support a callback to assert the workflow input
$workflowMock->assertDispatched(function ($input) {
    return $input['foo'] === 'bar';
});
```

### Mocking activities

Mocking activities works like workflows, but for activity you must provide interface and the method to mock.

```php
Temporal::fake();

$activityMock = Temporal::mockActivity([YourActivityInterface::class, 'activityMethod'])
    ->onTaskQueue('custom-queue'); // not required but useful for mocking and asserting that activity is executed on the correct queue
    ->andReturn('result');

// Your test code...

$activityMock->assertDispatched();
$activityMock->assertDispatchedTimes(1);
$activityMock->assertNotDispatched();

// All assertion method support a callback to assert the activity input
$activityMock->assertDispatched(function ($input) {
    return $input['foo'] === 'bar';
});
```

### Assertions

Dispatches assertions can be done through the `Temporal` facade but there are some downsides compared to the options above:

- You must provide the workflow/activity interface and method name, so this is duplicated
- If you want to ensure that the workflow/activity is executed on the correct queue, you must check the task queue yourself

```php
Temporal::assertWorkflowDispatched(YourWorkflowInterface::class, function($workflowInput, string $taskQueue) {
    return $workflowInput['foo'] === 'bar' && $taskQueue === 'custom-queue';
});

Temporal::assertActivityDispatched([YourActivityInterface::class, 'activityMethod'], function($activityInput, string $taskQueue) {
    return $activityInput['foo'] === 'bar' && $taskQueue === 'custom-queue';
});
```

## PHPStan

This package provides a PHPStan extension to improve the experience when working with Temporal proxy classes.

If you have [`phpstan/extension-installer`](https://github.com/phpstan/extension-installer) installed, you are ready to go.
Otherwise, you have to add the extension to your `phpstan.neon` file:

```neon
includes:
    - ./vendor/keepsuit/laravel-temporal/extension.neon
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Fabio Capucci](https://github.com/keepsuit)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
