<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Elasticsearch\IndicesController;
use App\Http\Controllers\Elasticsearch\CatController;
use App\Http\Controllers\Elasticsearch\BulkController;
use App\Http\Controllers\Elasticsearch\Transport\BulkController as TransportBulkController;
use App\Http\Controllers\Elasticsearch\Transport\SearchController as TransportSearchController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


// indices
Route::put('/indices/{index}/create', [IndicesController::class, 'create']);
Route::post('/indices/{index}/setAliases', [IndicesController::class, 'setAliases']);
Route::post('/indices/{index}/change', [IndicesController::class, 'change']);

//bulk
Route::post('/bulk/{index}/bulk', [BulkController::class, 'bulk']);
Route::post('/bulk/{index}/start', [BulkController::class, 'start']);
Route::post('/bulk/{index}/end', [BulkController::class, 'end']);

// for transport service only

//bulk
Route::post('/bulk/transport/setHotSpot', [TransportBulkController::class, 'setHotSpot']);

//search
Route::post('/search/transport/locations', [TransportSearchController::class, 'locations']);
Route::post('/search/transport/locationsByGEO', [TransportSearchController::class, 'locationsByGEO']);
Route::post('/search/transport/autoSuggestion', [TransportSearchController::class, 'autoSuggestion']);




//cat
Route::get('/cat/indices', [CatController::class, 'indices']);
Route::get('/cat/aliases', [CatController::class, 'aliases']);

