<?php

namespace App\Http\Controllers\V1\Transport;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Services\Elasticsearch\Transport\Search as EsSearchService;

class SearchController extends Controller{

    protected $EsSearch;
    protected $index;
    public function __construct(EsSearchService $EsSearch){
        $this->EsSearch = $EsSearch;
        $this->index = "transport";
    }

    public function transport(Request $request){

        $validator = Validator::make($request->all(), [
            'query' => 'required|string',
            'from' => 'nullable|integer',
            'size' => 'nullable|integer',
            'location_type' => ['required', Rule::in(config('elasticsearch.transport.location_type'))],
            'lang' => ['required', Rule::in(config('elasticsearch.language'))],
            'locale' => ['required', Rule::in(config('elasticsearch.locale'))],
            'source' => 'required|string',
            'country' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $index = $this->index;
        $query = $request->input('query');
        $from = $request->input('from');
        $size = $request->input('size');
        $location_type = $request->input('location_type');
        $lang = $request->input('lang');
        $locale = $request->input('locale');
        $source = $request->input('source');
        $country = $request->input('country');

        
        
        return $this->EsSearch->searchTransport($index, $query, $from, $size, $location_type, $lang, $locale, $source, $country);
    }

    public function searchLocation(Request $request, string $index){

        $validator = Validator::make($request->all(), [
            'parent_location_code' => 'required|string',
            'location_type' => ['required', Rule::in(config('elasticsearch.transport.location_type'))],
            'source' => 'required|string',
            'country' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $parent_location_code = $request->input('parent_location_code');
        $location_type = $request->input('location_type');
        $source = $request->input('source');
        $country = $request->input('country');

        
        return $this->EsSearch->searchLocation($index, $parent_location_code, $location_type, $source, $country);
    }

    public function searchLocationByGEO(Request $request, string $index){

        $validator = Validator::make($request->all(), [
            'geo' => 'array|size:2',
            'geo.*' => 'numeric',
            'radius' => 'nullable|numeric',
            'location_type' => ['required', Rule::in(config('elasticsearch.transport.location_type'))],
            'source' => 'required|string',
            'country' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $geo = $request->input('geo');
        $radius = $request->input('radius');
        
        return $this->EsIndices->endBulk($index);
    }

}