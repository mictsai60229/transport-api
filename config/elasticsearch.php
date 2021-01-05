<?php

return [
    'hosts' => [
        'default' => [
            'host' => '127.0.0.1',
            'port' => '9200',
            'scheme' => 'http'
            //'path' => '',
            //'user' => env('user'),
            //'pass' => env('pass')
        ]

        //more hosts
    ],

    'locale' => ['kk', 'tw', 'cn', 'hk', 'jp', 'kr', 'sg', 'my', 'th', 'vn', 'ph', 'id'],
    'language' => ['en', 'ja', 'ko', 'vi', 'th', 'zh-tw', 'zh-hk', 'zh-cn'],

    //api transport
    'transport' => [
        'location_type' => ['all', 'cities', 'area', 'bus_station']
    ] 

    
];