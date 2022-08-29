<?php

namespace Keepsuit\LaravelTemporal\Commands\Concerns;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Terminal;

/**
 * @mixin Command
 * @mixin \Illuminate\Console\Concerns\InteractsWithIO
 */
trait InteractsWithIO
{
    /**
     * The current terminal width.
     */
    protected ?int $terminalWidth = null;

    /**
     * A list of error messages that should be ignored.
     *
     * @var string[]
     */
    protected array $ignoreMessages = [
        'worker is allocated',
        'worker destroyed',
        'stderr',
    ];

    /**
     * Write a string as raw output.
     */
    public function raw(string $string): void
    {
        if (! Str::startsWith(trim($string), $this->ignoreMessages)) {
            $this->output->writeln($string);
        }
    }

    /**
     * Write information about a request to the console.
     *
     * @param  array{
     *              WorkflowType: array{Name:string},
     *              TaskQueueName: string,
     *              Attempt: int,
     *              WorkflowStartTime: string
     *         }  $workflowInfo
     */
    public function workflowInfo(array $workflowInfo, int|string|null $verbosity = null): void
    {
        $terminalWidth = $this->getTerminalWidth();

        $workflowName = $workflowInfo['WorkflowType']['Name'];
        // $taskQueue = $workflowInfo['TaskQueueName'];
        $attempt = $workflowInfo['Attempt'];
        $startAt = Carbon::parse($workflowInfo['WorkflowStartTime'])->toDateTimeString();

        if ($attempt > 1) {
            $dots = str_repeat('.', max($terminalWidth - strlen($workflowName.sprintf('(%d)', $attempt).$startAt) - 7, 3));

            $this->output->writeln(sprintf(
                '  <fg=cyan;options=bold>%s</> <fg=cyan>(%s)</>  <fg=#6C7280>%s  %s</>',
                $workflowName,
                $attempt,
                $dots,
                $startAt
            ), $this->parseVerbosity($verbosity));

            return;
        }

        $dots = str_repeat('.', max($terminalWidth - strlen($workflowName.$startAt) - 6, 3));

        $this->output->writeln(sprintf(
            '  <fg=cyan;options=bold>%s</>  <fg=#6C7280>%s  %s</>',
            $workflowName,
            $dots,
            $startAt
        ), $this->parseVerbosity($verbosity));
    }

    /**
     * Computes the terminal width.
     */
    protected function getTerminalWidth(): int
    {
        if ($this->terminalWidth == null) {
            $this->terminalWidth = (new Terminal())->getWidth();

            $this->terminalWidth = $this->terminalWidth >= 30
                ? $this->terminalWidth
                : 30;
        }

        return $this->terminalWidth;
    }
}
