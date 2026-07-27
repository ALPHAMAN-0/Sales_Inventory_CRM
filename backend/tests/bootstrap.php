<?php

/*
|--------------------------------------------------------------------------
| Test bootstrap — pin the suite to the dedicated testing schema
|--------------------------------------------------------------------------
| docker-compose injects backend/.env (APP_ENV=local, DB_DATABASE=sales_crm)
| as real OS environment variables, which PHP exposes via $_SERVER. Laravel's
| env repository reads $_SERVER *before* putenv, so PHPUnit's <env force> (which
| only rewrites putenv in this version) is not enough — without this file the
| suite would connect to, and RefreshDatabase would WIPE, the dev database.
|
| Setting $_SERVER/$_ENV here, before Laravel's LoadEnvironmentVariables runs,
| guarantees every test (and the app it boots) targets sales_crm_testing.
*/

require __DIR__.'/../vendor/autoload.php';

/** The canonical testing environment — also reused by spawned child processes. */
const TESTING_ENV = [
    'APP_ENV' => 'testing',
    'DB_CONNECTION' => 'mysql',
    'DB_HOST' => 'mysql',
    'DB_PORT' => '3306',
    'DB_DATABASE' => 'sales_crm_testing',
    'DB_USERNAME' => 'sales',
    'DB_PASSWORD' => 'secret',
    'BCRYPT_ROUNDS' => '4',
    'QUEUE_CONNECTION' => 'sync',
    'CACHE_STORE' => 'array',
    'SESSION_DRIVER' => 'array',
    'MAIL_MAILER' => 'array',
];

foreach (TESTING_ENV as $key => $value) {
    putenv("{$key}={$value}");
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}
