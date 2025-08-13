<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Keepsuit\LaravelTemporal\Support\RoadRunnerBinaryHelper;
use Keepsuit\LaravelTemporal\Support\ServerProcessInspector;
use Keepsuit\LaravelTemporal\Support\ServerStateFile;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Temporal\WorkerFactory;

#[AsCommand('temporal:work')]
class WorkCommand extends Command
{
    use Concerns\InteractsWithIO;
    use Concerns\RoadrunnerDependencies;

    protected $signature = 'temporal:work
                        {queue? : The name of the task queue to work}
                        {--rpc-host= : The RPC host the server should be available on}
                        {--rpc-port= : The RPC port the server should be available on}
                        {--workers=auto : The number of workers that should be available to handle requests}
                        {--max-jobs=500 : The number of jobs to process before reloading the server}
                        {--rr-config= : The path to the RoadRunner .rr.yaml file}
                        {--watch : Automatically reload the server when the application is modified}';

    protected $description = 'Start temporal worker';

    protected ?string $queue = null;

    public function handle(
        ServerStateFile $serverStateFile,
        ServerProcessInspector $inspector,
        RoadRunnerBinaryHelper $roadRunnerBinaryHelper
    ): ?int {
        $roadRunnerBinary = $this->ensureRoadRunnerBinaryIsInstalled($roadRunnerBinaryHelper);

        $configVersion = $this->detectRoadrunnerConfigVersion($roadRunnerBinaryHelper);

        $this->writeServerStateFile($serverStateFile);

        $this->queue = ($this->argument('queue') ?: config('temporal.queue')) ?: WorkerFactory::DEFAULT_TASK_QUEUE;

        $clientKey = config('temporal.tls.client_key');
        $clientCert = config('temporal.tls.client_cert');
        $rootCa = config('temporal.tls.root_ca');
        $serverName = config('temporal.tls.server_name');

        $server = new Process([
            $roadRunnerBinary,
            ...['-c', $this->configPath()],
            ...['-o', sprintf('version=%s', $configVersion)],
            ...['-o', sprintf('server.command=%s ./vendor/bin/roadrunner-temporal-worker', (new PhpExecutableFinder)->find())],
            ...['-o', sprintf('temporal.address=%s', config('temporal.address'))],
            ...['-o', sprintf('temporal.namespace=%s', config('temporal.namespace'))],
            ...(is_string($clientKey) && is_string($clientCert))
                ? ['-o', sprintf('temporal.tls.key=%s', $clientKey), '-o', sprintf('temporal.tls.cert=%s', $clientCert), '-o', 'temporal.tls.client_auth_type=require_and_verify_client_cert']
                : [],
            ...is_string($rootCa) ? ['-o', sprintf('temporal.tls.root_ca=%s', $rootCa)] : [],
            ...is_string($serverName) ? ['-o', sprintf('temporal.tls.server_name=%s', $serverName)] : [],
            ...$this->workerCount() > 0 ? ['-o', sprintf('temporal.activities.num_workers=%s', $this->workerCount())] : [],
            ...$this->maxJobs() > 0 ? ['-o', sprintf('temporal.activities.max_jobs=%s', $this->maxJobs())] : [],
            ...['-o', sprintf('rpc.listen=tcp://%s:%d', $this->rpcHost(), $this->rpcPort())],
            ...['-o', 'logs.mode=production'],
            ...['-o', $this->laravel->environment('local') ? 'logs.level=debug' : 'logs.level=warn'],
            ...['-o', 'logs.output=stdout'],
            ...['-o', 'logs.encoding=json'],
            'serve',
        ], base_path(), [
            'APP_ENV' => $this->laravel->environment(),
            'APP_BASE_PATH' => $this->laravel->basePath(),
            'LARAVEL_TEMPORAL' => 1,
            'TEMPORAL_QUEUE' => $this->queue,
        ]);

        $server->start();

        $serverStateFile->writeProcessId($server->getPid());

        return $this->runServer($server, $inspector);
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
     * Write the server state file.
     */
    protected function writeServerStateFile(ServerStateFile $serverStateFile): void
    {
        $serverStateFile->writeState([
            'appName' => config('app.name', 'Laravel'),
            'rpcHost' => $this->rpcHost(),
            'rpcPort' => $this->rpcPort(),
            'workers' => $this->workerCount(),
            'config' => config('temporal'),
        ]);
    }

    /**
     * Get the RPC host the server should be available on.
     */
    protected function rpcHost(): string
    {
        if (! is_string($this->option('rpc-host'))) {
            return '127.0.0.1';
        }

        return $this->option('rpc-host');
    }

    /**
     * Get the RPC port the server should be available on.
     */
    protected function rpcPort(): int
    {
        $rpcPort = $this->option('rpc-port');

        return is_numeric($rpcPort) ? (int) $rpcPort : 6001;
    }

    /**
     * Get the number of workers that should be started.
     */
    protected function workerCount(): int
    {
        if (is_numeric($this->option('workers'))) {
            return (int) $this->option('workers');
        }

        return 0;
    }

    /**
     * Get the number of workers that should be started.
     */
    protected function maxJobs(): int
    {
        if (is_numeric($this->option('max-jobs'))) {
            return (int) $this->option('max-jobs');
        }

        return 0;
    }

    /**
     * Get the path to the RoadRunner configuration file.
     */
    protected function configPath(): string
    {
        $path = $this->option('rr-config');

        if (is_string($path)) {
            return \Safe\realpath($path);
        }

        \Safe\touch(base_path('.rr.yaml'));

        return base_path('.rr.yaml');
    }

    /**
     * Run the given server process.
     */
    protected function runServer(Process $server, ServerProcessInspector $inspector): ?int
    {
        while (! $server->isStarted()) {
            \Safe\sleep(1);
        }

        $this->writeServerRunningMessage();

        $watcher = $this->startServerWatcher();

        while ($server->isRunning()) {
            $this->writeServerOutput($server);

            if ($watcher !== null) {
                if ($watcher->isRunning() && $watcher->getIncrementalOutput()) {
                    $this->components->info('Application change detected. Restarting workers…');

                    $inspector->reloadServer();
                } elseif ($watcher->isTerminated()) {
                    $this->error(
                        'Watcher process has terminated. Please ensure Node and chokidar are installed.'.PHP_EOL.
                        $watcher->getErrorOutput()
                    );

                    return Command::FAILURE;
                }
            }

            usleep(500 * 1000);
        }

        $this->writeServerOutput($server);

        $exitCode = $server->getExitCode();

        if ($exitCode === Command::FAILURE) {
            $this->components->error('The worker has crashed. Please, verify that the host can connect to the Temporal service.');
        }

        return $exitCode;
    }

    /**
     * Start the watcher process for the server.
     */
    protected function startServerWatcher(): ?Process
    {
        if (! $this->option('watch')) {
            return null;
        }

        /** @var string[] $paths */
        $paths = config('temporal.watch', []);

        if ($paths === []) {
            throw new InvalidArgumentException('List of directories/files to watch not found. Please update your "config/temporal.php" configuration file.');
        }

        return tap(new Process([
            (new ExecutableFinder)->find('node'),
            'file-watcher.cjs',
            \Safe\json_encode(collect($paths)->map(fn (string $path) => base_path($path)), JSON_THROW_ON_ERROR),
        ], \Safe\realpath(__DIR__.'/../../bin'), null, null, null))->start();
    }

    /**
     * Write the server start "message" to the console.
     */
    protected function writeServerRunningMessage(): void
    {
        $this->components->info(
            sprintf('Processing activities from the [%s] queue.', $this->queue)
        );

        $this->components->warn('Press Ctrl+C to stop the worker');
    }

    /**
     * Write the server process output to the console.
     */
    protected function writeServerOutput(Process $server): void
    {
        Str::of($server->getIncrementalOutput())
            ->explode("\n")
            ->filter()
            ->each(function ($output): void {
                try {
                    $debug = \Safe\json_decode($output, true, 512, JSON_THROW_ON_ERROR);
                } catch (Exception) {
                    return;
                }

                /**
                 * @var array{
                 *     level: string,
                 *     msg: string,
                 *     ts: float,
                 *     logger: string,
                 *     "workflow info"?: array{
                 *          WorkflowType: array{Name:string},
                 *          TaskQueueName: string,
                 *          Attempt: int,
                 *          WorkflowStartTime: string
                 *     }
                 * } $debug
                 */

                $level = trim($debug['level']);
                $logger = trim($debug['logger']);
                $message = trim($debug['msg']);

                $ignoreMessages = [
                    'destroy signal received',
                    'req-resp mode',
                    'scan command',
                    'sending stop request to the worker',
                    'stop signal received, grace timeout is: ',
                    'exit forced',
                    'worker allocated',
                    'worker is allocated',
                    'worker constructed',
                    'worker destructed',
                    'worker destroyed',
                    '[INFO] RoadRunner server started; version:',
                    '[INFO] sdnotify: not notified',
                    'exiting; byeee!!',
                    'storage cleaning happened too recently',
                    'write error',
                    'unable to determine directory for user configuration; falling back to current directory',
                    '$HOME environment variable is empty',
                    'unable to get instance ID',
                ];

                if ($logger !== 'temporal' && $debug['msg'] && ! in_array($debug['msg'], $ignoreMessages)) {
                    $this->info($output);
                    return;
                }

                if($message=== 'workflow registered') {
                    $this->info("Workflow Registered : Queue: ".$debug['taskqueue']." Workflow: ".$debug['workflow name']);
                }

                if($message=== 'activity registered') {
                    $this->info("Activity Registered : Queue: ".$debug['taskqueue']." Activity: ".$debug['workflow name']);
                }

                if ($message === 'workflow execute' && isset($debug['workflow info'])) {
                    $this->workflowInfo($debug['workflow info']);
                }
            });

        Str::of($server->getIncrementalErrorOutput())
            ->explode("\n")
            ->filter()
            ->each(function ($output): void {
                if (! Str::contains($output, ['DEBUG', 'INFO', 'WARN'])) {
                    $this->error($output);
                }
            });
    }

    protected function detectRoadrunnerConfigVersion(RoadRunnerBinaryHelper $roadRunnerBinaryHelper): string
    {
        try {
            return $roadRunnerBinaryHelper->configVersion();
        } catch (\Throwable) {
            $this->warn('Your RoadRunner binary version may be incompatible with laravel temporal.');

            return '2.7';
        }
    }
}
