<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ActivityMakeCommand extends GeneratorCommand
{
    protected $name = 'temporal:make:activity';

    protected $description = 'Create a temporal activity';

    protected $type = 'Activity';

    public function handle(): ?bool
    {
        $this->callSilently(ActivityInterfaceMakeCommand::class, [
            'name' => $this->getNameInput(),
            '--local' => $this->option('local'),
        ]);

        return parent::handle();
    }

    protected function getStub(): string
    {
        return $this->resolveStubPath('/stubs/activity.stub');
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace.'\Activities';
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
