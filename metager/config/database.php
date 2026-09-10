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
            /*
             * Reads go to a replica, writes to the primary.
             *
             * CloudNativePG publishes three Services for a cluster: `-rw`
             * points at whichever pod is currently primary, `-r` at every
             * instance that is Ready, and `-ro` at the replicas only. A
             * planned switchover — what a node drain triggers — takes writes
             * away for five to fifteen seconds while one instance is demoted
             * and another promoted. Reads never have to stop for that: the
             * replicas serve the whole time. Splitting the connection is what
             * lets the app notice.
             *
             * DB_HOST stays the write host and DB_READ_HOST names the read
             * one. Point it at `-r`, not `-ro`: CNPG drops an instance from
             * both Services the moment it stops being Ready, so during a
             * switchover `-r` is *already* just the replicas — while `-ro`
             * has no endpoints at all on a cluster momentarily down to one
             * instance, which would turn a write outage into a total one.
             * `-ro` is the right answer only if the primary must never serve
             * a read at all, and that is a load decision, not an availability
             * one.
             *
             * Left unset, DB_READ_HOST leaves this connection exactly as it
             * was: one host, one PDO. That is what docker-compose, the review
             * environments and the test suite get, and it is also the way to
             * turn the split off again without a deploy.
             *
             * `sticky` keeps a request that has written on the primary for
             * the rest of that request, so nothing renders from a replica
             * that has not caught up with what the same request just wrote.
             * Lag is still visible *between* requests — POST, redirect, GET —
             * which streaming replication on a healthy cluster answers in
             * single-digit milliseconds and a browser redirect does not, but
             * it is not zero.
             *
             * Note that sticky is per Connection object and nothing in the
             * framework ever clears it, so a long-running process pins itself
             * to the primary on its first write and never reads a replica
             * again. App\Console\Commands\ScheduleWorker clears it once a
             * minute for exactly that reason.
             */
            ...(env('DB_READ_HOST') ? [
                'read' => [
                    'host' => env('DB_READ_HOST'),
                    'port' => env('DB_READ_PORT', env('DB_PORT', '5432')),
                ],
                'write' => [
                    'host' => env('DB_HOST', 'localhost'),
                    'port' => env('DB_PORT', '5432'),
                ],
                'sticky' => env('DB_STICKY', true),
            ] : []),
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

        // read_write_timeout -1 means "block in the socket read for ever".
        // It has to stay that way here: callers on this connection make
        // genuinely long blocking calls — AnonymousToken blpops for 30s,
        // AnonymousTokenPayment for a caller-supplied duration, and
        // EngineOrchestrator brpops for WAIT_SECONDS on the result page — and a
        // bounded timeout would abort them mid-wait.
        //
        // The cost is that a peer which stops answering is indistinguishable
        // from one that has nothing to say yet. See the 'fetcher' connection
        // below for where that mattered.
        'default' => [
            'read_write_timeout' => -1,
            'host' => env('REDIS_HOST', 'localhost'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
            'cluster' => false,
        ],

        /*
         * The fetch worker's own connection: same server, bounded timeouts.
         *
         * `requests:fetcher` is a single-replica daemon that loops on Redis for
         * the life of the pod, and it is the whole of MetaGer's search — if it
         * stops consuming, every result page renders empty. On 2026-09-10 a
         * node's container runtime died, taking the Valkey master pod with it
         * without a preStop hook and so without closing its sockets. The
         * worker's blpop went into a socket that would never answer and never
         * be reset, and with read_write_timeout -1 it blocked there for ever:
         * no exception, so App\Support\RedisFailover never saw a failover to
         * retry, and the loop never came back round. The process stayed alive,
         * so `pgrep` reported it healthy. Search was down for fifteen minutes.
         *
         * Node drains are routine — several a week — so this has to be survived
         * rather than detected. The worker can afford what the connection above
         * cannot: its only blocking call is `blpop(FETCHQUEUE_KEY, 1)`, a
         * one-second wait, so a read timeout a few times that is pure upside.
         * A peer that has stopped answering now surfaces as
         * Predis\TimeoutException — a CommunicationException, which
         * RedisFailover already treats as a failover — and the retry drops the
         * socket and reconnects through the master proxy, which by then has
         * seen the promotion.
         *
         * Tunable by env without a deploy, in case a briefly slow Valkey ever
         * makes this flap: every timeout costs a reconnect.
         */
        'fetcher' => [
            'read_write_timeout' => env('REDIS_FETCHER_READ_TIMEOUT', 3.0),
            // Bounded too, and much tighter. This is only ever paid on a
            // reconnect, which is exactly when the far side may be gone: the
            // Predis default of 5s would make recovering from a dead master
            // slower than noticing it.
            'timeout' => env('REDIS_FETCHER_CONNECT_TIMEOUT', 1.0),
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