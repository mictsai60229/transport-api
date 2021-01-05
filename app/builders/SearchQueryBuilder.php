<?php

namespace App\Builders;


class SearchQueryBuilder{

    protected $body;
    public function construct(){

        $this->body = [];
    }

    public function addIndex(string $index){

        $this->body['index'] = $index;
    }

    public function addPagination(int $from, int $size){
        
        $from = max(0, $from);
        $size = min(50000, $size);

        $this->body['from'] = $from;
        $this->body['size'] = $size;
    }

    public function addSort(string $field, string $order){
        
        $this->body['sort'][] = [$field => $order];
    }

    public function addSortAsc(string $field){

        $this->addSort($field, 'asc');
    }

    public function addSortDesc(string $field){
        
        $this->addSort($field, 'desc');
    }

    public function addFields(array $fields){

        $this->body['fields'] = $fields;
    }

}