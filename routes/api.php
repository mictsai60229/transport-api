<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Elasticsearch\IndicesController;
use App\Http\Controllers\Elasticsearch\CatController;
use App\Http\Controllers\Elasticsearch\BulkController;

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
Route::put('/indices/create', [IndicesController::class, 'create']);
Route::post('/indices/startBulk', [IndicesController::class, 'startBulk']);
Route::post('/indices/endBulk', [IndicesController::class, 'endBulk']);
Route::delete('/indices/deleteIndices', [IndicesController::class, 'deleteIndices']);
Route::post('/indices/setAliases', [IndicesController::class, 'setAliases']);
Route::post('/indices/setAliasesLatest', [IndicesController::class, 'changeIndices']);

//bulk
Route::post('/bulk/bulk', [BulkController::class, 'bulk']);


//cat
Route::get('/cat/indices', [CatController::class, 'indices']);
Route::get('/cat/aliases', [CatController::class, 'aliases']);

