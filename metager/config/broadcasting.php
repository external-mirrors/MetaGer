<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the connections defined in the "connections" array below.
    |
    | Supported: "reverb", "pusher", "ably", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_CONNECTION', 'reverb'),

    /*
    |--------------------------------------------------------------------------
    | Where A Queued Broadcast Is Pushed
    |--------------------------------------------------------------------------
    |
    | A ShouldBroadcast event is not delivered inline: BroadcastManager::queue()
    | pushes a BroadcastEvent job and a worker does the delivery. Which *queue
    | connection* that push goes to is normally the application default — and
    | that is the problem this key exists to solve.
    |
    | QUEUE_CONNECTION is unset in production, so config/queue.php falls back to
    | `database`, and the push became a synchronous Postgres INSERT on whatever
    | request happened to dispatch the event. For App\Events\KeyChanged that is
    | the start page and the result page, neither of which needs a database for
    | anything else (see tests/Feature/KeyChangedNeedsNoDatabaseTest). Making
    | the event ShouldRescue stopped that failing the page; it did not stop it
    | reaching for Postgres, nor paying DB_CONNECT_TIMEOUT for the attempt while
    | a user waits.
    |
    | Naming the connection here rather than moving QUEUE_CONNECTION wholesale
    | is deliberate: the default queue carries donation and mail jobs, and the
    | Valkey the chart deploys is explicitly a cache with no persistence
    | (chart/values.yaml). Those jobs must stay somewhere durable. A broadcast
    | is the opposite — a missed one costs a live-updating balance one refresh —
    | so it is exactly the payload that belongs on the volatile store.
    |
    | Its own queue name, too, so the worker draining it serves this and nothing
    | else: chart/templates/deployment-queue.yaml runs a second `queue:work`
    | against precisely this connection/queue pair. Changing either value here
    | without changing that worker means the jobs are pushed and never run.
    |
    */

    'queue_connection' => env('BROADCAST_QUEUE_CONNECTION', 'redis'),

    'queue' => env('BROADCAST_QUEUE', 'broadcasts'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other systems or over WebSockets. Samples of
    | each available type of connection are provided inside this array.
    |
    */

    'connections' => [

        'reverb' => [
            'driver' => 'reverb',
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'app_id' => env('REVERB_APP_ID'),
            'options' => [
                'host' => env('REVERB_HOST'),
                'port' => env('REVERB_PORT', 443),
                'scheme' => env('REVERB_SCHEME', 'https'),
                'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
            ],
            'client_options' => [
                // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
            ],
        ],

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'host' => env('PUSHER_HOST') ?: 'api-' . env('PUSHER_APP_CLUSTER', 'mt1') . '.pusher.com',
                'port' => env('PUSHER_PORT', 443),
                'scheme' => env('PUSHER_SCHEME', 'https'),
                'encrypted' => true,
                'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
                'base_path' => '/ws'
            ],
            'client_options' => [
                // Guzzle client options: https://docs.guzzlephp.org/en/stable/request-options.html
            ],
        ],

        'ably' => [
            'driver' => 'ably',
            'key' => env('ABLY_KEY'),
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
