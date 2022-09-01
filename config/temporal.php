<?php

return [
    /**
     * Temporal server address
     */
    'address' => env('TEMPORAL_ADDRESS', 'localhost:7233'),

    /**
     * Default task queue
     */
    'queue' => \Temporal\WorkerFactory::DEFAULT_TASK_QUEUE,

    'workflows' => [

    ],

    'activities' => [

    ],

    /**
     * Directories to watch when server is started with `--watch` flag
     */
    'watch' => [
        'app',
        'config',
    ],

    /**
     * Options for test workers
     */
    'testing' => [
        /**
         * Additional queue to register the worker (required for mocking activities)
         */
        'queues' => [],
    ],
];
