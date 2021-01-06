<?php

namespace App\Http\Controllers\V1\Transport;

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

    public function setHotSpot(Request $request, string $index){

        $validator = Validator::make($request->all(), [
            'location_codes' => 'required|boolean',
            'location_type' => ['required', Rule::in(config('elasticsearch.transport.location_type'))],
            'locale' => ['required', Rule::in(config('elasticsearch.locale'))],
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $location_codes = $request->input('location_codes');
        $location_type = $request->input('location_type');
        $locale = $request->input('locale');
        
        return $this->EsBulk->setHotSpot($location_codes, $location_type, $locale);
    }
}