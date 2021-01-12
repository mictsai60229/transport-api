<?php

namespace App\Formatters\Response\Common;

use Illuminate\Support\Arr;
use App\Formatters\Response\ResponseFormatter;

class IndicesCreateResponse extends ResponseFormatter{
    
    protected $es_response;
    protected $req_params;

    public function __construct(array $es_response, array $req_params){
        
        $this->es_response = $es_response;
        $this->req_params = $req_params;
    }

    public function getData(){
        
        $data = [];
        $data['index'] = Arr::get($this->es_response, 'index');
        $data['delete'] = Arr::get($this->req_params, 'delete');

        return $data;
    }
}