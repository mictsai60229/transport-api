<?php

namespace App\Repositories\Elasticsearch;


class Cat extends Connection{
    
    public function getCat(){

        return $this->getClient()->cat();
    }


    public function aliases($params){
        
        return $this->getCat()->aliases($params); 
    }

    public function indices($params){
        
        return $this->getCat()->indices($params); 
    }

};