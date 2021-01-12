<?php

namespace Database\Factories;
use Faker\Factory as Faker;

class ESBaseFactory{

    private $batch_size;
    private $faker;

    function __construct(){
        $batch_size = 1;
        $faker = Faker::create("en_us");
    }

    
    public function create(array $values = []){
        
        if ($batch_size > 1){
            return $this->batchCreate($values);   
        }
        
        return $this->defintition();
        
    }

    public function batchCreate(){

        $actions = [];
        foreach (range(0, $batch_size) as $i){
            $actions[] = $this->defintition();
        }
        $batch_size = 1;
        return $actions;
    }

    public function count(int $batch_size){

        $this->batch_size = $batch_size;
        return $this;
    }

    public function defintition(){

        return [
            "_id" => $this->uuid,
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];

    }
}


