<?php

namespace App\Services\Elasticsearch\Transport;


use App\Services\Elasticsearch\Indices;

Class Search extends Indices{

    public function searchTransport(string $index, string $query, int $from, int $size, string $location_type, string $lang, string $locale, string $source, string $country){

        $params = [
            'index' => $index,
            'from' => $from,
            'size' => $size,
            'body' => [
                'query' => [
                    'bool' => [
                        'filter' => [
                            ["term" => ["location_type" => $location_type]],
                            ["term" => ["source" => $source]],
                            ["term" => ["country" => $country]]
                        ],
                        'must' => [
                            ['match' => ["language.{$lang}.name" => $query]]
                        ]
                    ]
                ],
                'sort' => [
                    ["locale.{$locale}.location_score" => 'desc'],
                    ['language.en.name.raw' => 'asc']
                ]
            ]           
        ];

        return $this->EsIndicesRepo->search($params);
    }
    
}