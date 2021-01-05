<?php

namespace App\Repositories\Elasticsearch;

use Elasticsearch\ClientBuilder;

class Connection{

    public function __construct(string $config = 'default'){
        $this->config = $config;
    }

    public function getClient(){
        return ClientBuilder::create()
            ->setHosts(config("elasticsearch.hosts.{$this->config}"))
            ->build();
    }
    
}