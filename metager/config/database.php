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
        ],
        'useragents' => [
            'driver' => 'sqlite',
            'database' => database_path('databases/' . env('SQLITE_DATABASE', 'database.sqlite')),
            'prefix' => '',
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
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
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
        ],
        'logs' => [
            'driver' => 'pgsql',
            'host' => env('LOGS_DB_HOST', 'localhost'),
            'port' => env('LOGS_DB_PORT', '5432'),
            'database' => env('LOGS_DB_DATABASE', 'forge'),
            'username' => env('LOGS_DB_USERNAME', 'forge'),
            'password' => env('LOGS_DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'search_path' => 'public',
            'sslmode' => 'prefer',
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

        'client' => env('REDIS_CLIENT', 'phpredis'),
        'cluster' => false,
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
            'password' => env('REDIS_CACHE_PASSWORD', null),
            'port' => env('REDIS_CACHE_PORT', 6379),
            'database' => 0,
            'cluster' => false,
        ],

        'sentinel' => [
            [
                'host' => env('REDIS_SENTINEL_HOST', 'localhost'),
                'port' => env('REDIS_SENTINEL_PORT', 26379),
                'password' => env('REDIS_SENTINEL_PASSWORD', ''),
            ],
            'options' => [
                'service' => env('REDIS_SENTINEL_SERVICE', 'metager'),
                'replication' => 'sentinel',

                'parameters' => [
                    'password' => env('REDIS_SENTINEL_PASSWORD', ''),
                    'database' => 0,
                ]
            ]
        ],

        "clusters" => [
            'clustercache' => [
                [
                    'host' => env('REDIS_CACHE_HOST', 'localhost'),
                    'port' => env('REDIS_CACHE_PORT', 6379),
                    'password' => env('REDIS_CACHE_PASSWORD', ''),

                ]
            ],
            'options' => [
                'cluster' => 'redis',
                'parameters' => [
                    'database' => 0,
                    'password' => env('REDIS_CACHE_PASSWORD', null),
                ]
            ],
        ]


    ],

];