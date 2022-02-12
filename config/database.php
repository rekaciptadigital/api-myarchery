<?php

return [
    'default' => 'mysql',
    'migrations' => 'migrations',
  'connections' => [
    'mysql' => [
      'driver' => 'mysql',
      'host' => env('DB_HOST'),
      'database' => env('DB_DATABASE'),
      'username' => env('DB_USERNAME'),
      'password' => env('DB_PASSWORD'),
      'charset'   => 'utf8',
      'collation' => 'utf8_unicode_ci',
      'strict'    => false,
    ],
  ],
  'migrations' => 'migrations',
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
