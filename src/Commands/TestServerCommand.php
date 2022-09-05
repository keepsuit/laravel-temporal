<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Illuminate\Console\Command;
use Keepsuit\LaravelTemporal\Testing\TemporalTestingServer;
use Symfony\Component\Console\Output\OutputInterface;

class TestServerCommand extends Command
{
    use Concerns\InteractsWithIO;

    protected $signature = 'temporal:server
                        {--port=7233 : The port the temporal server should be available on}';

    protected $description = 'Start a temporal test server';

    protected ?TemporalTestingServer $temporalServer = null;

    public function handle(): ?int
    {
        $this->temporalServer = TemporalTestingServer::create();

        if ($this->verbosity > OutputInterface::VERBOSITY_NORMAL) {
            $this->temporalServer->setDebugOutput(true);
        }

        $this->temporalServer->start($this->option('port'));

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
        $this->components->info('Temporal testing server is running');

        $this->components->warn('Press Ctrl+C to stop the server');
    }
}
