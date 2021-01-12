<?php

namespace App\Formatters\Document;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


#transform
#validation


abstract class AbstractDocumentFormatter{

    public $_name = "base";
    abstract public function getValidationRules();
    abstract public function transform(array $data);
    abstract public function preprocess(array $data);
    

    public function process(array $data, string $range="all"){

        $data = $this->validate($data, $range);
        if (empty($data)){
            return $data;
        }
        $data = $this->transform($data);

        return $data;
    }
    /*
    *
    *@retrun 
    */
    public function validate(array $data, string $range="all"){

        $validator = $this->getValidator($data, $range);
        if ($validator->fails()){

            $error_message = json_encode($validator->errors());
            Log::error("{$this->_name} formatter validation error on _id \"{$data['_id']}\" : {$error_message}");

            return null;
        }

        return $data;
    }


    public function getValidator(array $data, string $range){

        if ($range === "all"){
            return Validator::make($data, $this->getValidationRules());
        }
        else if($range === "part"){

            $validation_rules = [];
            foreach (array_intersect_key($data, $this->getValidationRules()) as $key){
                $validation_rules[$key] = $this->validation_rules[$key];
            }
            return Validator::make($data, $validation_rules);
        }
    }
    
}