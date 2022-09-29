<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ActivityInterfaceMakeCommand extends GeneratorCommand
{
    protected $signature = 'temporal:make:activity-interface {name}
                            {--local : Create a local activity}
                            {--scoped : Create the activity inside a scoped directory}';

    protected $description = 'Create a temporal activity interface';

    protected $type = 'Activity';

    protected $hidden = true;

    protected function getStub(): string
    {
        return $this->option('local')
            ? $this->resolveStubPath('/stubs/local_activity_interface.stub')
            : $this->resolveStubPath('/stubs/activity_interface.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        if ($this->option('scoped')) {
            $activityName = Str::replaceLast('Interface', '', $this->getNameInput());
            $namespace = Str::endsWith('Activity', $activityName)
                ? Str::replaceLast('Activity', '', $activityName)
                : $activityName;

            return sprintf('%s\\Activities\\%s', $rootNamespace, $namespace);
        }

        return sprintf('%s\\Activities', $rootNamespace);
    }

    /**
     * Resolve the fully-qualified path to the stub.
     */
    protected function resolveStubPath(string $stub): string
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
            ? $customPath
            : __DIR__.$stub;
    }

    protected function getNameInput(): string
    {
        $nameInput = parent::getNameInput();

        return Str::endsWith($nameInput, 'Interface')
            ? $nameInput
            : sprintf('%sInterface', $nameInput);
    }

    protected function getOptions(): array
    {
        return [
            ['local', null, InputOption::VALUE_NONE, 'Indicates that activity should be local'],
        ];
    }
}
