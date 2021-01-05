<?php

namespace App\Repositories\Elasticsearch;

use Elasticsearch\ClientBuilder;

class Document extends Connection{

    public function index($params){
        
        return $this->getClient()->index($params);
    }

    public function get($params){

        return $this->getClient()->get($params);
    }

    public function update($params){

        return $this->getClient()->update($params);
    }

    public function delete($params){

        return $this->getClient()->delete($params);
    }

    public function search($params){

        return $this->getClient()->search($params);
    }
}