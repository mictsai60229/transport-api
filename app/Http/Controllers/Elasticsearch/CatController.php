<?php

namespace App\Http\Controllers\Elasticsearch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Repositories\Elasticsearch\Cat as EsCatRepo;


class CatController extends Controller{

    protected $EsCat;
    public function __construct(EsCatRepo $EsCat){
        $this->EsCat = $EsCat;
    }

    public function aliases(Request $request){

        $params = $request->all();

        return $this->EsCat->aliases($params);
    }

    public function indices(Request $request){

        $params = $request->all();

        return $this->EsCat->indices($params);
    }

    
}