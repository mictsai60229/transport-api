<?php

namespace App\Http\Controllers\V1\Common;

use App\Http\Controllers\Controller;
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
        
        $validator = Validator::make($request->all(), [
            'backup_count' => 'nullable|numeric|min:0',
            'force' => 'nullable|boolean',
            'docs_threshold' => 'nullable|numeric|between:0,1'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $backup_count = $request->input('backup_count', 0);
        $force = $request->input('force', null);
        $docs_threshold = $force?0.0:(float)$request->input('docs_threshold', 0.7);
        

        return $this->es_indices->create($index, $backup_count, $docs_threshold);
    }


    public function change(Request $request, string $index){

        $validator = Validator::make($request->all(), [
            'target_index' => 'nullable|string',
            'force' => 'nullable|boolean',
            'docs_threshold' => 'nullable|numeric|between:0,1'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $target_index = $request->input('target_index', null);
        $force = $request->input('force', null);
        $docs_threshold = $force?0.0:(float)$request->input('docs_threshold', 0.7);


        return $this->es_indices->setAliases($index, $target_index, $docs_threshold);
        
    }

}
