<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});

Route::get('fund/evaluate/{nocache?}', 'FundController@evaluate');
Route::get('funds/{code}/history', 'FundController@history');
Route::get('funds/{code}/event', 'FundController@event');
Route::resource('funds', 'FundController', ['only' => [
    'index', 'show',
]]);

Route::get('stocks/{symbol}/candlesticks', 'StockController@candlesticks');
Route::get('stocks/{symbol}/values', 'StockController@values');
Route::resource('stocks', 'StockController', ['only' => [
    'index', 'show',
]]);
