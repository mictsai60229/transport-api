<?php

return [
    'success' => [
        'status' => '0000',
        'desc' => 'Success',
    ],

    'system_error' => [
        'status' => '9999',
        'desc' => 'System Error',
    ],

    'http_error' => [
        'status' => '9998',
        'desc' => 'Http Error',
    ],

    '404_not_found' => [
        'status' => '404',
        'desc' => 'Api Not Found',
    ],

    'invalid_input' => [
        'status' => '1000',
        'desc' => 'Invalid Input',
    ],

    'search_engine_error' => [
        'status' => '2000',
        'desc' => 'Search Engine Error',
    ],

    'database_error' => [
        'status' => '2001',
        'desc' => 'Database Error',
    ],

    'cache_error' => [
        'status' => '2002',
        'desc' => 'Cache Error',
    ],

    'redis_error' => [
        'status' => '2003',
        'desc' => 'Redis Error',
    ],

    'common_api_error' => [
        'status' => '3000',
        'desc' => 'Common Api Error',
    ],
];