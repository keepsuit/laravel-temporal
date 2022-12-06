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
     * Default retry policy
     */
    'retry' => [

        /**
         * Default retry policy for workflows
         */
        'workflow' => [
            /**
             * Initial retry interval (in seconds)
             * Default: 1
             */
            'initial_interval' => null,

            /**
             * Retry interval increment
             * Default: 2.0
             */
            'backoff_coefficient' => null,

            /**
             * Maximum interval before fail
             * Default: 100 x initial_interval
             */
            'maximum_interval' => null,

            /**
             * Maximum attempts
             * Default: unlimited
             */
            'maximum_attempts' => null,
        ],

        /**
         * Default retry policy for activities
         */
        'activity' => [
            /**
             * Initial retry interval (in seconds)
             * Default: 1
             */
            'initial_interval' => null,

            /**
             * Retry interval increment
             * Default: 2.0
             */
            'backoff_coefficient' => null,

            /**
             * Maximum interval before fail
             * Default: 100 x initial_interval
             */
            'maximum_interval' => null,

            /**
             * Maximum attempts
             * Default: unlimited
             */
            'maximum_attempts' => null,
        ],
    ],

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

    /**
     * Integrations options
     */
    'integrations' => [

        /**
         * Eloquent models serialization/deserialization options
         */
        'eloquent' => [
            /**
             * Default attribute key case conversion when serialize a model before sending to temporal.
             * Supported values: 'snake', 'camel', null.
             */
            'serialize_attribute_case' => null,

            /**
             * Default attribute key case conversion when deserializing payload received from temporal.
             * Supported values: 'snake', 'camel', null.
             */
            'deserialize_attribute_case' => null,

            /**
             * If true adds additional metadata fields (`__exists`, `__dirty`) to the serialized model to improve deserialization.
             * `__exists`: indicate that the model is saved to database.
             * `__dirty`: indicate that the model has unsaved changes. (original values are not included in the serialized payload but the deserialized model will be marked as dirty)
             */
            'include_metadata_field' => false,
        ],
    ],
];
