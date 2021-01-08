<?php

namespace App\Services\Elasticsearch\Transport;


use App\Services\Elasticsearch\Indices;

Class Bulk extends Indices{

    public function setHotSpot(string $index, array $location_codes, string $location_type, string $locale){

        $indexLatest = "{$index}-latest";
        // check {$index}-latest is setted
        if (empty($this->catAlias($indexLatest))){
            throw new CommonApiException("Index with name {$index}-latest doesn't exist.");
        }


        return ["scuesss" => [], "failure" => []];

    }
    
}