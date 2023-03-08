<?php

namespace Keepsuit\LaravelTemporal\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Keepsuit\LaravelTemporal\Support\RoadRunnerBinaryHelper;
use Keepsuit\LaravelTemporal\Support\ServerProcessInspector;
use Keepsuit\LaravelTemporal\Support\ServerStateFile;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;
use Temporal\WorkerFactory;

class WorkCommand extends Command
{
    use Concerns\InteractsWithIO;

    protected $signature = 'temporal:work
                        {queue? : The name of the task queue to work}
                        {--rpc-host= : The RPC host the server should be available on}
                        {--rpc-port= : The RPC port the server should be available on}
                        {--workers=auto : The number of workers that should be available to handle requests}
                        {--max-jobs=500 : The number of jobs to process before reloading the server}
                        {--max-memory=100 : The maximum amount of memory usage before reloading the server}
                        {--rr-config= : The path to the RoadRunner .rr.yaml file}
                        {--watch : Automatically reload the server when the application is modified}';

    protected $description = 'Start temporal worker';

    protected ?string $queue = null;

    public function handle(
        ServerStateFile $serverStateFile,
        ServerProcessInspector $inspector,
        RoadRunnerBinaryHelper $roadRunnerBinaryFinder
    ): ?int {
        $roadRunnerBinary = $roadRunnerBinaryFinder->binaryPath();

        $this->writeServerStateFile($serverStateFile);

        $this->queue = ($this->argument('queue') ?: $this->laravel['config']['temporal.queue']) ?: WorkerFactory::DEFAULT_TASK_QUEUE;

        $server = new Process([
            $roadRunnerBinary,
            ...['-c', $this->configPath()],
            ...['-o', 'version=2.7'],
            ...['-o', sprintf('server.command=%s ./vendor/bin/roadrunner-temporal-worker', (new PhpExecutableFinder())->find())],
            ...['-o', sprintf('temporal.address=%s', config('temporal.address'))],
            ...['-o', sprintf('temporal.namespace=%s', config('temporal.namespace'))],
            ...$this->option('workers') === 'auto' ? [] : ['-o', sprintf('temporal.activities.num_workers=%s', $this->option('workers'))],
            ...$this->option('max-jobs') === '0' ? [] : ['-o', sprintf('temporal.activities.max_jobs=%s', $this->option('max-jobs'))],
            ...['-o', sprintf('rpc.listen=tcp://%s:%d', $this->rpcHost(), $this->rpcPort())],
            ...['-o', 'logs.mode=production'],
            ...['-o', app()->environment('local') ? 'logs.level=debug' : 'logs.level=warn'],
            ...['-o', 'logs.output=stdout'],
            ...['-o', 'logs.encoding=json'],
            'serve',
        ], base_path(), [
            'APP_ENV' => app()->environment(),
            'APP_BASE_PATH' => base_path(),
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
        return $this->option('rpc-host') ?: '127.0.0.1';
    }

    /**
     * Get the RPC port the server should be available on.
     */
    protected function rpcPort(): int
    {
        return $this->option('rpc-port') ?: 6001;
    }

    /**
     * Get the number of workers that should be started.
     */
    protected function workerCount(): int|string
    {
        return $this->option('workers') == 'auto' ? 0 : $this->option('workers');
    }

    /**
     * Get the path to the RoadRunner configuration file.
     */
    protected function configPath(): string
    {
        $path = $this->option('rr-config');

        if (! $path) {
            touch(base_path('.rr.yaml'));

            return base_path('.rr.yaml');
        }

        if (! realpath($path)) {
            throw new InvalidArgumentException('Unable to locate specified configuration file.');
        }

        return realpath($path);
    }

    /**
     * Run the given server process.
     */
    protected function runServer(Process $server, ServerProcessInspector $inspector): ?int
    {
        while (! $server->isStarted()) {
            sleep(1);
        }

        $this->writeServerRunningMessage();

        $watcher = $this->startServerWatcher();

        while ($server->isRunning()) {
            $this->writeServerOutput($server);

            if ($watcher->isRunning() &&
                $watcher->getIncrementalOutput()) {
                $this->components->info('Application change detected. Restarting workers…');

                $inspector->reloadServer();
            } elseif ($watcher->isTerminated()) {
                $this->error(
                    'Watcher process has terminated. Please ensure Node and chokidar are installed.'.PHP_EOL.
                    $watcher->getErrorOutput()
                );

                return 1;
            }

            usleep(500 * 1000);
        }

        $this->writeServerOutput($server);

        return $server->getExitCode();
    }

    /**
     * Start the watcher process for the server.
     *
     * @return Process|object
     */
    protected function startServerWatcher()
    {
        if (! $this->option('watch')) {
            return new class()
            {
                public function __call(string $method, mixed $parameters): mixed
                {
                    return null;
                }
            };
        }

        /** @var string[] $paths */
        $paths = config('temporal.watch', []);

        if ($paths === []) {
            throw new InvalidArgumentException('List of directories/files to watch not found. Please update your "config/temporal.php" configuration file.');
        }

        return tap(new Process([
            (new ExecutableFinder())->find('node'),
            'file-watcher.js',
            json_encode(collect($paths)->map(fn (string $path) => base_path($path)), JSON_THROW_ON_ERROR),
        ], realpath(__DIR__.'/../../bin'), null, null, null))->start();
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
                    $debug = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
                } catch (Exception) {
                    return;
                }

                if (! is_array($debug)) {
                    $this->info($output);

                    return;
                }

                /** @var array{level:string,msg:string,ts:float,logger:string,"workflow info"?:array} $debug */

                // $level = trim($debug['level']);
                $logger = trim($debug['logger']);
                $message = trim($debug['msg']);

                if ($logger !== 'temporal') {
                    return;
                }

                if ($message === 'workflow execute') {
                    $this->workflowInfo($debug['workflow info']);
                }
            });

        Str::of($server->getIncrementalErrorOutput())
            ->explode("\n")
            ->filter()
            ->each(function ($output): void {
                // Only unusable output is provided

                // if (! Str::contains($output, ['DEBUG', 'INFO', 'WARN'])) {
                //     $this->components->error($output);
                // }
            });
    }
}
