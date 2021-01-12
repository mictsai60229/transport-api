<?php

namespace App\Http\Controllers\V1\Common;

use App\Http\Controllers\ApiController as Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Services\Elasticsearch\Bulk as EsBulkService;

class BulkController extends Controller{

    protected $es_bulk;
    public function __construct(EsBulkService $es_bulk){
        $this->es_bulk = $es_bulk;
    }

    public function bulk(Request $request, string $index){

        $validation_rules = [
            'action_type' => ['required', Rule::in(['index', 'delete', 'update'])],
            'body' => 'required'
        ];

        $this->validateRequest($request, $validation_rules);

        $req_params = [];

        $req_params['index'] = $index;
        $req_params['action_type'] = $request->input('action_type');
        $req_params['actions'] = $request->input('body');
        $req_params['validate_range'] = ($req_params['action_type'] === "index")? "all": "part";

        $response_formatter = $this->es_bulk->bulk($req_params);
        return $this->renderApiResonse($response_formatter);
    }

    public function start(Request $request, string $index){
        
        $validation_rules = [
            'target_index' => 'nullable|string'
        ];

        $this->validateRequest($request, $validation_rules);

        $req_params = [];

        $req_params['index'] = $index;
        $req_params['target_index'] = $request->input('target_index', null);

        $response_formatter =$this->es_bulk->start($req_params);
        return $this->renderApiResonse($response_formatter);
    }

    public function end(Request $request, string $index){

        $req_params = [];

        $req_params['index'] = $index;

        $response_formatter =$this->es_bulk->end($req_params);
        return $this->renderApiResonse($response_formatter);
    }


}