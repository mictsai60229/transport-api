<?php

namespace App\Formatters\Response\Common;

use Illuminate\Support\Arr;
use App\Formatters\Response\ResponseFormatter;

class BulkBulkResponse extends ResponseFormatter{
    
    protected $es_response;
    protected $req_params;

    public function __construct(array $es_response, array $req_params){
        
        $this->es_response = $es_response;
        $this->req_params = $req_params;
    }

    public function getData(){
        
        $data = [];
        $data['invalid'] = Arr::get($this->req_params, 'invalid');
        $data['fail'] = [];
        if (isset($this->es_response['errors']) && $this->es_response['errors']){
            foreach ($this->es_response['items'] as $item){
                if (isset($item['error'])){
                    $data['fail'][] = $item['_id'];
                    $error_message = json_encode($item['error']);
                    Log::error("bulk error on _id \"{$item['_id']}\" : {$error_message}");
                }
            }
        }

        return $data;
    }
}