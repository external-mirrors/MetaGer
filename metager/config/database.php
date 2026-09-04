<?php

return [

    /*
   |--------------------------------------------------------------------------
   | Default Database Connection Name
   |--------------------------------------------------------------------------
   |
   | Here you may specify which of the database connections below you wish
   | to use as your default connection for database operations. This is
   | the connection which will be utilized unless another connection
   | is explicitly specified when you execute a query / statement.
   |
   */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => database_path('databases/' . env('SQLITE_DATABASE', 'database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'engine' => 'InnoDB'
        ],
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            // Pdo\Mysql::ATTR_SSL_CA rather than PDO::MYSQL_ATTR_SSL_CA: PHP 8.5
            // deprecates the old constant, and composer.json now requires ^8.4,
            // which is where the Pdo\Mysql class was introduced.
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Pdo\Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'search_path' => 'public',
            'sslmode' => 'prefer',
            // Search-facing requests never touch this connection (see
            // App\QueryLogger — search logging only ever writes to Redis;
            // the SQL write is a separate, scheduled batch job), but the
            // scheduler and the settings/membership/donation pages do, and
            // PDO has no connect timeout by default: a Postgres outage left
            // those hang for the OS's own TCP timeout rather than failing
            // fast. On the scheduler that delay pushed the next `heartbeat`
            // past SchedulerHeartbeat::MAX_AGE_IN_MINUTES, so the liveness
            // probe restarted the pod over a problem a restart cannot fix
            // (GlitchTip METAGER-M/N — the trigger, `logs:create-invoice`,
            // not the mechanism). On the app pod, enough requests each
            // hanging this long exhausts FPM's shared worker pool, taking
            // down unrelated routes and the health-check probe with it.
            'options' => [
                \PDO::ATTR_TIMEOUT => env('DB_CONNECT_TIMEOUT', 3),
            ],
        ],
        // A restored dump of the WordPress/CiviCRM database, read-only, only for
        // `artisan assoc:import-civicrm` (App\Console\Commands\ImportCiviCrm). Not
        // present in a fresh checkout; CIVICRM_DB_DATABASE is unset until a dump is
        // loaded somewhere reachable.
        'civicrm' => [
            'driver' => 'mysql',
            'host' => env('CIVICRM_DB_HOST', 'localhost'),
            'port' => env('CIVICRM_DB_PORT', '3306'),
            'database' => env('CIVICRM_DB_DATABASE', ''),
            'username' => env('CIVICRM_DB_USERNAME', ''),
            'password' => env('CIVICRM_DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'predis'),

        'default' => [
            'read_write_timeout' => -1,
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
            'cluster' => false,
        ],

        'cache' => [
            'host' => env('REDIS_CACHE_HOST', 'localhost'),
            'port' => env('REDIS_CACHE_PORT', 6379),
            'password' => env('REDIS_CACHE_PASSWORD', null),
        ],

        // Predis tries these one at a time and gives up with
        // "No sentinel server available for autodiscovery" the moment the
        // one it is currently trying fails to connect — it does not retry
        // that exception (see App\MetaGer's PredisException catch), so with
        // a single entry any transient blip reaching it is fatal even
        // though the other two sentinels are perfectly healthy (GlitchTip
        // METAGER-I/L). REDIS_SENTINEL_HOSTS is a comma-separated list of
        // `host[:port]` so the chart can name every replica's own stable
        // DNS name (the subchart's headless Service) instead of the load-
        // balancing Service — falls back to the single-host vars for
        // docker-compose, where there is exactly one sentinel.
        'sentinel' => [
            ...collect(explode(',', env('REDIS_SENTINEL_HOSTS', env('REDIS_SENTINEL_HOST', 'localhost'))))
                ->map(fn($entry) => trim($entry))
                ->filter()
                ->map(function ($entry) {
                    [$host, $port] = str_contains($entry, ':')
                        ? explode(':', $entry, 2)
                        : [$entry, env('REDIS_SENTINEL_PORT', 26379)];

                    return [
                        'host' => $host,
                        'port' => (int) $port,
                        'password' => env('REDIS_SENTINEL_PASSWORD', null),
                        'timeout' => env('REDIS_CONNECT_TIMEOUT', 0.2),
                    ];
                })
                ->all(),
            'options' => [
                'service' => env('REDIS_SENTINEL_SERVICE', 'mymaster'),
                'replication' => 'sentinel',
                'password' => env('REDIS_SENTINEL_REDIS_PASSWORD', null),
                'parameters' => [
                    'password' => env('REDIS_SENTINEL_REDIS_PASSWORD', null),
                    'database' => 0
                ]
            ]
        ],

    ],

];