<?php

return [

    'default' => 'local',

    'cloud' => 's3',

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'customer_uploads' => [
            'driver' => 'local',
            'root' => storage_path('app/public/excel_report'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => public_path('storage'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],

        'uploads' => [
            'driver' => 'local',
            'root' => '/tes',
        ],
        'links' => [
            public_path('storage') => storage_path('app/public'),
            public_path('images') => storage_path('app/images'),
        ],

    ],

    'storage' => [
        'driver' => 'local',
        'root'   => storage_path(),
    ],

    'customer_uploads' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
    ],
   

];