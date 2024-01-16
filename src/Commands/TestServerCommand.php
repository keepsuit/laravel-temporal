<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\Command;
use Keepsuit\LaravelTemporal\Testing\LocalTemporalServer;
use Keepsuit\LaravelTemporal\Testing\TemporalServer;
use Keepsuit\LaravelTemporal\Testing\TimeSkippingTemporalServer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand('temporal:server')]
class TestServerCommand extends Command
{
    use Concerns\InteractsWithIO;

    protected $signature = 'temporal:server
                        {--port=7233 : The port the temporal server should be available on}
                        {--enable-time-skipping : Enable time skipping}';

    protected $description = 'Start a temporal test server';

    protected ?TemporalServer $temporalServer = null;

    public function handle(): ?int
    {
        $this->temporalServer = match ($this->option('enable-time-skipping')) {
            true => TimeSkippingTemporalServer::create(),
            default => LocalTemporalServer::create(),
        };

        if ($this->verbosity > OutputInterface::VERBOSITY_NORMAL) {
            $this->temporalServer->setDebugOutput(true);
        }

        $this->temporalServer->start((int) $this->option('port'));

        $this->writeServerRunningMessage();

        do {
            sleep(1);
        } while ($this->temporalServer->isRunning());

        return 0;
    }

    /**
     * Returns the list of signals to subscribe.
     *
     * @return int[]
     */
    public function getSubscribedSignals(): array
    {
        return [SIGINT, SIGTERM];
    }

    /**
     * Write the server start "message" to the console.
     */
    protected function writeServerRunningMessage(): void
    {
        $this->components->info(sprintf('Temporal testing server running on port: %s', (int) $this->option('port')));

        if ($this->temporalServer instanceof LocalTemporalServer) {
            $this->components->info(sprintf('Temporal ui available at: [http://127.0.0.1:%s]', (int) $this->option('port') + 1000));
        }

        $this->comment('  <fg=yellow;options=bold>Press Ctrl+C to stop the server</>');
    }
}
