<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand('temporal:make:activity')]
class ActivityMakeCommand extends GeneratorCommand
{
    use Concerns\Stubs;

    protected $name = 'temporal:make:activity';

    protected $description = 'Create a new temporal activity class';

    protected $type = 'Activity';

    protected function getStub(): string
    {
        return match (true) {
            $this->option('interface') && $this->option('local') => $this->resolveStubPath('local_activity_interface.stub'),
            $this->option('interface') => $this->resolveStubPath('activity_interface.stub'),
            $this->option('local') => $this->resolveStubPath('local_activity.stub'),
            default => $this->resolveStubPath('activity.stub'),
        };
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        $rootNamespace = match (true) {
            is_dir($this->laravel->path('Temporal/Activities')) => $rootNamespace.'\\Temporal',
            ! is_dir($this->laravel->path('Activities')) => $rootNamespace.'\\Temporal',
            default => $rootNamespace
        };

        if (is_string($this->option('for-workflow'))) {
            $namespace = Str::of($this->option('for-workflow'))
                ->whenEndsWith('Workflow', fn ($name) => $name->replaceLast('Workflow', ''));

            return sprintf('%s\\Workflows\\%s', $rootNamespace, $namespace);
        }

        if ($this->option('scoped')) {
            $namespace = Str::of($this->getNameInput())
                ->whenEndsWith('Activity', fn ($name) => $name->replaceLast('Activity', ''));

            return sprintf('%s\\Activities\\%s', $rootNamespace, $namespace);
        }

        return sprintf('%s\\Activities', $rootNamespace);
    }

    protected function getNameInput(): string
    {
        return Str::of(parent::getNameInput())
            ->whenEndsWith('Interface', fn ($name) => $name->replaceLast('Interface', ''))
            ->when($this->option('interface'), fn ($name) => $name->append('Interface'));
    }

    protected function getOptions(): array
    {
        return [
            ['interface', 'i', InputOption::VALUE_NONE, 'Create an interface for the activity instead of a class'],
            ['local', 'l', InputOption::VALUE_NONE, 'Create a local activity'],
            ['scoped', 's', InputOption::VALUE_NONE, 'Create the activity inside a scoped directory'],
            ['for-workflow', 'w', InputOption::VALUE_REQUIRED, 'Create the activity in the provided workflow namespace'],
            ['force', 'f', InputOption::VALUE_NONE, 'Create the Interceptor class even if the file already exists'],
        ];
    }
}
