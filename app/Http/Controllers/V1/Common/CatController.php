<?php

namespace App\Http\Controllers\V1\Common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Elasticsearch\Cat as EsCatRepo;


class CatController extends Controller{

    protected $es_cat;
    public function __construct(EsCatRepo $es_cat){
        $this->es_cat = $es_cat;
    }

    public function aliases(Request $request){

        $params = $request->all();

        return $this->es_cat->aliases($params);
    }

    public function indices(Request $request){

        $params = $request->all();

        return $this->es_cat->indices($params);
    }

    
}