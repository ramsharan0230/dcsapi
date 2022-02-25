<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace'=>'Api', 'prefix'=>'admin', 'as'=>'admin.'], function () {
    // currency management
    Route::resource('currencies', 'CurrencyController');
    Route::get('currency/{slug}', 'CurrencyController@getCurrencyBySlug');

    //stock management
    Route::resource('stocks', 'StockController');
    Route::get('currency/{slug}', 'CurrencyController@getCurrencyBySlug');

    //currency buy
    Route::post('buycurrency', 'CurrencyController@buyCurrency');
    Route::get('{id}/bought-currency', 'CurrencyController@boughtCurrency');
});

