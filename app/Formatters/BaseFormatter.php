<?php

namespace App\Formatters;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


#transform
#validation


class BaseFormatter{

    protected $transformFunctions = [];
    protected $validationRules = [];
    public $_name = "base";

    /*
    *
    *@retrun 
    */
    public function validate(array $data, string $range="all"){

        $validator = $this->getValidator($data, $range);
        if ($validator->fails()){

            $validatorErrorMessages = json_encode($validator->errors());
            Log::error("{$this->_name} Formatter Error on : {$validatorErrorMessages}");

            return null;
        }

        return $this->transform($data);
    }


    public function transform(array $data){

        foreach (array_intersect_key($data, $this->transformFunctions) as $key){
            $data[$key] = $this->transformFunctions[$key]($data[$key]);
        }

        return $data;
    }

    public function getValidator(array $data, string $range){

        if ($range === "all"){
            return Validator::make($data, $this->validationRules);
        }
        else if($range === "part"){

            $validationRules = [];
            foreach (array_intersect_key($data, $this->validationRules) as $key){
                $validationRules[$key] = $this->validationRules[$key];
            }
            return Validator::make($data, $validationRules);
        }
    }
    
}