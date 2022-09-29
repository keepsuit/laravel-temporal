<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ActivityMakeCommand extends GeneratorCommand
{
    protected $signature = 'temporal:make:activity {name}
                            {--local : Create a local activity}
                            {--scoped : Create the activity inside a scoped directory}
                            {--for-workflow= : Create the activity in the provided workflow namespace}';

    protected $description = 'Create a temporal activity';

    protected $type = 'Activity';

    public function handle(): ?bool
    {
        $this->callSilently(ActivityInterfaceMakeCommand::class, [
            'name' => $this->getNameInput(),
            '--local' => $this->option('local'),
            '--scoped' => $this->option('scoped'),
            '--for-workflow' => $this->option('for-workflow'),
        ]);

        return parent::handle();
    }

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/activity.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        if ($this->option('for-workflow') !== null) {
            $namespace = Str::endsWith('Workflow', $this->option('for-workflow'))
                ? Str::replaceLast('Workflow', '', $this->option('for-workflow'))
                : $this->option('for-workflow');

            return sprintf('%s\\Workflows\\%s', $rootNamespace, $namespace);
        }

        if ($this->option('scoped')) {
            $activityName = $this->getNameInput();
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
            ? Str::replaceLast($nameInput, 'Interface', '')
            : $nameInput;
    }

    protected function getOptions(): array
    {
        return [
            ['local', null, InputOption::VALUE_NONE, 'Indicates that activity should be local'],
        ];
    }
}
