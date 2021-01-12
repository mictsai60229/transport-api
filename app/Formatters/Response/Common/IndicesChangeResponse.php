<?php

namespace App\Formatters\Response\Common;

use Illuminate\Support\Arr;
use App\Formatters\Response\ResponseFormatter;

class IndicesChangeResponse extends ResponseFormatter{
    
    protected $es_response;
    protected $req_params;

    public function __construct(array $es_response, array $req_params){
        
        $this->es_response = $es_response;
        $this->req_params = $req_params;
    }

    public function getData(){
        
        $data = [];
        $data['index'] = Arr::get($this->req_params, 'index');
        if (isset($this->req_params['old_index'])){
            $data['old_index'] = Arr::get($this->req_params, 'old_index');
        }

        return $data;
    }
}