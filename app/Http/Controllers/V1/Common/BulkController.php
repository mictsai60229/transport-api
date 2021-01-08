<?php

namespace App\Http\Controllers\V1\Common;

use App\Http\Controllers\Controller;
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

        $validator = Validator::make($request->all(), [
            'action_type' => ['required', Rule::in(['index', 'delete', 'update'])],
            'body' => 'required'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $action_type = $request->input('action_type');
        $actions = $request->input('body');
        $validate_range = ($action_type === "index")?"all":"part";

        return $this->es_bulk->bulk($index, $action_type, $actions, $validate_range);
    }

    public function start(Request $request, string $index){
        
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

        return $this->es_bulk->start($index, $target_index, $docs_threshold);
    }

    public function end(Request $request, string $index){

        return $this->es_bulk->end($index);
    }


}