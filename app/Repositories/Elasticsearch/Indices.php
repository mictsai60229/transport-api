<?php

namespace App\Repositories\Elasticsearch;


class Indices extends Connection{

    public function getIndices(){

        return $this->getClient()->indices();
    }

    public function create(array $params = []){

        return $this->getIndices()->create($params);
    }

    public function delete(array $params = []){
        
        return $this->getIndices()->delete($params);
    }

    public function bulk($params){
        return $this->getClient()->bulk($params);
    }

    public function putSettings(array $params = []){

        return $this->getIndices()->putSettings($params);
    }

    public function getSettings(array $params = []){

        return $this->getIndices()->getSettings($params);
    }

    public function putMappings(array $params = []){

        return $this->getIndices()->putMappings($params);
    }

    public function getMappings(array $params = []){

        return $this->getIndices()->getMappings($params);
    }
    
    public function updateAliases(array $params = []){

        return $this->getIndices()->updateAliases($params);
    }

    public function getAliases(array $params = []){

        return $this->getIndices()->getAliases($params);
    }

    public function refresh(array $params = []){

        return $this->getIndices()->refresh($params);
    }

}