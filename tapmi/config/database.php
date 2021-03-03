<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

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
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],
		
		'mobile_ins' => [
			'driver'   => 'oracle',
			'tns'      =>  '',
			/*'host'     => 'dboracle.tap-agri.com',*/
			'host'     => 'dboracledev.tap-agri.com',
			'port'     => '1521',
			'database' => 'tapapps',
			'username' => 'mobile_inspection',
			'password' => 'mobile_inspection',
			'charset'  => 'AL32UTF8',
			'prefix'   => '',
		],  
        
		'ebcc' => [
			'driver'   => 'oracle',
			'tns'      =>  '',
			'host'     => 'dboracledev.tap-agri.com',
			'port'     => '1521',
			'database' => 'tapapps',
			'username' => 'ebcc',
			'password' => 'ebcc',
			'charset'  => 'AL32UTF8',
			'prefix'   => '',
        ],

        'tapdw' => [
			'driver'   => 'oracle',
			'tns'      =>  '',
			'host'     => 'devdw.tap-agri.com',
			'port'     => '1521',
			'database' => 'tapdw',
			'username' => 'tap_dw',
			'password' => 'tapdw123#',
			'charset'  => 'AL32UTF8',
			'prefix'   => '',
		],
        'mongodb_hectarstatment' => [
            'driver'   => 'mongodb',
            'host'     => env('DB_HOST', 'dbmongoqa.tap-agri.com'),
            'port'     => env('DB_PORT', 4848),
            'database' => env('DB_DATABASE', 's_hectare_statement'),
            'username' => env('DB_USERNAME', 's_hectare_statement'),
            'password' => env('DB_PASSWORD', 'h52019'),
            'options' => [
                'database' => 's_hectare_statement' 
            ]
        ],
        'mongodb_auth' => [
            'driver'   => 'mongodb',
            'host'     => env('DB_HOST', 'dbmongoqa.tap-agri.com'),
            'port'     => env('DB_PORT', 4848),
            'database' => env('DB_DATABASE', 's_auth'),
            'username' => env('DB_USERNAME', 's_auth'),
            'password' => env('DB_PASSWORD', '4uth2019'),
            'options' => [
                'database' => 's_auth' 
            ]
        ],

		  /*'mobile_ins' => [
                        'driver'   => 'oracle',
                        'tns'      =>  '',
                        'host'     => 'dboracle.tap-agri.com',
                        'port'     => '1521',
                        'database' => 'tapapps',
                        'username' => 'mobile_inspection',
                        'password' => 'mobile_inspection',
                        'charset'  => 'AL32UTF8',
                        'prefix'   => '',
                ],*/

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
