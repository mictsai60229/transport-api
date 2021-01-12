<?php

namespace App\Formatters\Document;

use App\Formatters\Document\AbstractDocumentFormatter;
use Illuminate\Validation\Rule;



#transform
#validation


class TransportFormatter extends AbstractDocumentFormatter{

    public $_name = "transport";

    public function getValidationRules(){

        return [
            '_id' => 'required',
            'location_code' => 'required',
            'location_type' => ['required', Rule::in(config('elasticsearch.transport.location_type'))],
            'locale.*.location_score' => 'required|integer',
            'language.*.name' => 'required',
            'coodinates' => 'nullable',
            'coodinates.*' => 'numeric',
            'parent_location_code' => 'required',
            'source' => 'required',
            'country' => 'required'
        ];

    }
    

    public function transform(array $data){
        return $data;
    }


    public function preprocess(array $data){
        
        if (!isset($data['_id'])){
            $data['_id'] = "{$data['source']}-{$data['location_type']}-{$data['location_code']}";
        }
        return $data;
    }

    
}