<?php

return [
    /**
     * Temporal server address
     */
    'address' => env('TEMPORAL_ADDRESS', 'localhost:7233'),

    /**
     * Temporal namespace
     */
    'namespace' => env('TEMPORAL_NAMESPACE', \Temporal\Client\ClientOptions::DEFAULT_NAMESPACE),

    /**
     * Default task queue
     */
    'queue' => \Temporal\WorkerFactory::DEFAULT_TASK_QUEUE,

    /**
     * Manual register workflows
     */
    'workflows' => [
    ],

    /**
     * Manual register activities
     */
    'activities' => [
    ],

    /**
     * Directories to watch when server is started with `--watch` flag
     */
    'watch' => [
        'app',
        'config',
    ],
];
