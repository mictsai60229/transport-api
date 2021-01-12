<?php

namespace App\Http\Controllers\V1\Common;

use App\Http\Controllers\ApiController as Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Services\Elasticsearch\Indices as EsIndicesService;


class IndicesController extends Controller{

    protected $es_indices;
    public function __construct(EsIndicesService $es_indices){
        $this->es_indices = $es_indices;
    }
    /**
     * Undocumented function
     *
     * @param Request $request
     * @param string $index
     * @return void
     */
    public function create(Request $request, string $index){
        
        $validation_rules = [
            'backup_count' => 'nullable|numeric|min:0',
            'force' => 'nullable|boolean',
            'docs_threshold' => 'nullable|numeric|between:0,1'
        ];

        $this->validateRequest($request, $validation_rules);

        $req_params = [];
        
        $force = $request->input('force', null);
        $req_params['index'] = $index;
        $req_params['backup_count'] = $request->input('backup_count', 0);
        $req_params['docs_threshold'] = $force?0.0:(float)$request->input('docs_threshold', 0.7);

        $response_formatter = $this->es_indices->create($req_params);
        return $this->renderApiResonse($response_formatter);
    }


    public function change(Request $request, string $index){

        $validation_rules = [
            'target_index' => 'nullable|string',
            'force' => 'nullable|boolean',
            'docs_threshold' => 'nullable|numeric|between:0,1'
        ];

        $this->validateRequest($request, $validation_rules);

        $req_params = [];

        $force = $request->input('force', null);
        $req_params['index'] = $index;
        $req_params['target_index'] = $request->input('target_index', null);
        $req_params['docs_threshold'] = $force?0.0:(float)$request->input('docs_threshold', 0.7);

        $response_formatter = $this->es_indices->change($req_params);
        return $this->renderApiResonse($response_formatter);
        
    }

}
