<?php

namespace App\Http\Controllers\Elasticsearch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Services\Elasticsearch\Bulk as EsBulkService;

class BulkController extends Controller{

    protected $EsBulk;
    public function __construct(EsBulkService $EsBulk){
        $this->EsBulk = $EsBulk;
    }

    public function bulk(Request $request, string $index){

        $validator = Validator::make($request->all(), [
            'actionType' => ['required', Rule::in(['index', 'delete', 'update'])],
            'body' => 'required'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $actionType = $request->input('actionType');
        $actions = $request->input('body');
        $validateRange = ($request->input('actionType') === "index")?"all":"part";

        return $this->EsBulk->bulk($index, $actionType, $actions, $validateRange);
    }

    public function start(Request $request, string $index){
        
        $validator = Validator::make($request->all(), [
            'force' => 'nullable|boolean',
            'threshold' => 'nullable|numeric|between:0,1'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $force = $request->input('force', null);
        $docsThreshold = $force?0.0:(float)$request->input('threshold', 0.7);

        return $this->EsBulk->start($index, $docsThreshold);
    }

    public function end(Request $request, string $index){

        return $this->EsBulk->end($index);
    }


}