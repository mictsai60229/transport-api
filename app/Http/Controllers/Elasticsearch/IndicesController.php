<?php

namespace App\Http\Controllers\Elasticsearch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Services\Elasticsearch\Indices as EsIndicesService;


class IndicesController extends Controller{

    protected $EsIndices;
    public function __construct(EsIndicesService $EsIndices){
        $this->EsIndices = $EsIndices;
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
            'backupCount' => 'nullable|numeric|min:0',
            'force' => 'nullable|boolean',
            'docthreshold' => 'nullable|numeric|between:0,1'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $backupCount = $request->input('backupCount', 1);
        $force = $request->input('force', null);
        $docsThreshold = $force?0.0:(float)$request->input('threshold', 0.7);
        

        return $this->EsIndices->create($index, $backupCount, $docsThreshold);
    }

    public function setAliases(Request $request, string $index){

        $validator = Validator::make($request->all(), [
            'alias' => 'required'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $alias = $request->input('alias');

        return $this->EsIndices->setAliases($index, $alias);
    }

    public function change(Request $request, string $index){

        $validator = Validator::make($request->all(), [
            'alias' => 'nullable',
            'force' => 'nullable|boolean',
            'threshold' => 'nullable|numeric|between:0,1'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $alias = $request->input('alias', null);
        $force = $request->input('force', null);
        $docsThreshold = $force?0.0:(float)$request->input('threshold', 0.7);


        return $this->EsIndices->setAliasesLatest($index, $docsThreshold);
        
    }

}
