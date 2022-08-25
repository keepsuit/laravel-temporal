<?php

namespace Keepsuit\LaravelTemporal\Support;

use RuntimeException;
use Symfony\Component\Process\Process;

class ServerProcessInspector
{
    public function __construct(
        protected ServerStateFile $serverStateFile,
        protected SymfonyProcessFactory $processFactory,
        protected PosixExtension $posix,
        protected RoadRunnerBinaryFinder $roadRunnerFinder
    ) {
    }

    /**
     * Determine if the RoadRunner server process is running.
     */
    public function serverIsRunning(): bool
    {
        [
            'masterProcessId' => $masterProcessId,
        ] = $this->serverStateFile->read();

        return $masterProcessId && $this->posix->kill($masterProcessId, 0);
    }

    /**
     * Reload the RoadRunner workers.
     */
    public function reloadServer(): void
    {
        [
            'state' => [
                'host' => $host,
                'rpcPort' => $rpcPort,
            ],
        ] = $this->serverStateFile->read();

        $process = $this->processFactory->createProcess([
            $this->roadRunnerFinder->binaryPath(),
            'reset',
            '-o',
            sprintf('rpc.listen=tcp://%s:%s', $host, $rpcPort),
        ], base_path());

        $process->start();

        $process->waitUntil(function ($type, $buffer): bool {
            if ($type === Process::ERR) {
                throw new RuntimeException('Cannot reload RoadRunner: '.$buffer);
            }

            return true;
        });
    }

    /**
     * Stop the RoadRunner server.
     */
    public function stopServer(): bool
    {
        [
            'masterProcessId' => $masterProcessId,
        ] = $this->serverStateFile->read();

        return $this->posix->kill($masterProcessId, SIGTERM);
    }
}
