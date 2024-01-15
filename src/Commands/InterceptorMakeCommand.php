<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;
use Temporal\Interceptor\ActivityInboundInterceptor;
use Temporal\Interceptor\GrpcClientInterceptor;
use Temporal\Interceptor\Trait\ActivityInboundInterceptorTrait;
use Temporal\Interceptor\Trait\WorkflowClientCallsInterceptorTrait;
use Temporal\Interceptor\Trait\WorkflowInboundCallsInterceptorTrait;
use Temporal\Interceptor\Trait\WorkflowOutboundCallsInterceptorTrait;
use Temporal\Interceptor\Trait\WorkflowOutboundRequestInterceptorTrait;
use Temporal\Interceptor\WorkflowClientCallsInterceptor;
use Temporal\Interceptor\WorkflowInboundCallsInterceptor;
use Temporal\Interceptor\WorkflowOutboundCallsInterceptor;
use Temporal\Interceptor\WorkflowOutboundRequestInterceptor;

#[AsCommand('temporal:make:interceptor')]
class InterceptorMakeCommand extends GeneratorCommand
{
    use Concerns\Stubs;

    protected $name = 'temporal:make:interceptor';

    protected $description = 'Create a new temporal interceptor class';

    protected $type = 'Interceptor';

    protected const INTERCEPTOR_TYPES = [
        'workflow_client_calls',
        'workflow_inbound_calls',
        'workflow_outbound_calls',
        'activity_inbound',
        'grpc_client',
        'workflow_outbound_request',
    ];

    protected ?array $typesInput = null;

    protected function getStub(): string
    {
        return $this->resolveStubPath('interceptor.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return sprintf('%s\\Temporal\\Interceptors', $rootNamespace);
    }

    protected function buildClass($name): string
    {
        $stub = parent::buildClass($name);

        $types = $this->getTypesInput();

        $interfaces = Collection::make($types)
            ->map(fn (string $type) => match ($type) {
                'workflow_client_calls' => WorkflowClientCallsInterceptor::class,
                'workflow_inbound_calls' => WorkflowInboundCallsInterceptor::class,
                'workflow_outbound_calls' => WorkflowOutboundCallsInterceptor::class,
                'activity_inbound' => ActivityInboundInterceptor::class,
                'grpc_client' => GRPCClientInterceptor::class,
                'workflow_outbound_request' => WorkflowOutboundRequestInterceptor::class,
                default => null,
            })
            ->filter();

        $traits = Collection::make($types)
            ->map(fn (string $type) => match ($type) {
                'workflow_client_calls' => WorkflowClientCallsInterceptorTrait::class,
                'workflow_inbound_calls' => WorkflowInboundCallsInterceptorTrait::class,
                'workflow_outbound_calls' => WorkflowOutboundCallsInterceptorTrait::class,
                'activity_inbound' => ActivityInboundInterceptorTrait::class,
                'workflow_outbound_request' => WorkflowOutboundRequestInterceptorTrait::class,
                default => null,
            })
            ->filter();

        $stub = str_replace(
            '{{ interfaces }}',
            $interfaces->map(fn (string $interface) => class_basename($interface))->implode(', '),
            $stub
        );

        $stub = str_replace(
            '{{ traits }}',
            $traits->map(fn (string $trait) => sprintf('use %s;', class_basename($trait)))->implode(PHP_EOL.'    '),
            $stub
        );

        return str_replace(
            '{{ imports }}',
            $interfaces->merge($traits)
                ->map(fn (string $interface) => sprintf('use %s;', $interface))
                ->implode(PHP_EOL),
            $stub
        );
    }

    protected function getOptions(): array
    {
        return [
            [
                'type',
                't',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Temporal interceptor type',
                null,
                self::INTERCEPTOR_TYPES,
            ],
            [
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Create the Interceptor class even if the file already exists',
            ],
        ];
    }

    protected function getTypesInput(): array
    {
        if ($this->typesInput !== null) {
            return $this->typesInput;
        }

        $types = array_filter(
            $this->option('type') ?? [],
            fn (string $type) => in_array($type, self::INTERCEPTOR_TYPES, strict: true)
        );

        if ($types !== []) {
            return $this->typesInput = $types;
        }

        $types = $this->choice(
            question: 'Select Temporal interceptor types to implement',
            choices: self::INTERCEPTOR_TYPES,
            multiple: true
        );
        assert(is_array($types));

        return $this->typesInput = $types;
    }
}
