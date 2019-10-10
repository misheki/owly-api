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

Route::post('oauth/token', 'AuthController@auth');
Route::post('password/forgot', 'AuthController@forgotPassword');
Route::post('password/reset', 'AuthController@resetPassword');


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:api']], function () {

    Route::get('merchant', 'AccountController@merchant');

    Route::get('daily/{date}', 'RecordController@daily');
    Route::post('record/addconfirm', 'RecordController@addConfirm');
    Route::post('record/add', 'RecordController@add');
    Route::get('record/{id}/edit', 'RecordController@edit'); //Retrieve
    Route::put('record/{id}', 'RecordController@update');
    Route::delete('record/{id}', 'RecordController@delete');

    Route::get('worker/list', 'WorkerController@list');
    Route::post('worker/add', 'WorkerController@add');
    Route::get('worker/{id}/edit', 'WorkerController@edit'); //Retrieve
    Route::put('worker/{id}', 'WorkerController@update');
    Route::delete('worker/{id}', 'WorkerController@delete');
    

});
