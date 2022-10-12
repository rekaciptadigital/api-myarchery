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
            'url' => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => 'your-key',
            'secret' => 'your-secret',
            'region' => 'your-region',
            'bucket' => 'your-bucket',
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
