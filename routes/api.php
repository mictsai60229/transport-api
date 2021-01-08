<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\V1\Common\IndicesController;
use App\Http\Controllers\V1\Common\CatController;
use App\Http\Controllers\V1\Common\BulkController;
use App\Http\Controllers\V1\Transport\BulkController as TransportBulkController;
use App\Http\Controllers\V1\Transport\SearchController as TransportSearchController;



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


Route::namespace('v1')->prefix('v1')->group(function () {


    //for transport index
    //bulk
    Route::namespace('v1/transport')->prefix('transport')->group(function () {
        Route::post('/bulk/setHotSpot', [TransportBulkController::class, 'setHotSpot']);

        //search
        Route::post('/search/transport', [TransportSearchController::class, 'transport']);
    });

    //common
    // indices
    Route::put('/{index}/indices/create', [IndicesController::class, 'create']);
    Route::post('/{index}/indices/setAliases', [IndicesController::class, 'setAliases']);
    Route::post('/{index}/indices/change', [IndicesController::class, 'change']);

    //bulk
    Route::post('/{index}/bulk/bulk', [BulkController::class, 'bulk']);
    Route::post('/{index}/bulk/start', [BulkController::class, 'start']);
    Route::post('/{index}/bulk/end', [BulkController::class, 'end']);
    //cat
    Route::get('/cat/indices', [CatController::class, 'indices']);
    Route::get('/cat/aliases', [CatController::class, 'aliases']);

});

