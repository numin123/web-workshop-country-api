<?php

use App\Models\User;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['jwt.auth','api-header']], function () {

    // all routes to protected resources are registered here
    Route::get('users/list', function(){
        $users = User::all();

        $response = ['success'=>true, 'data'=>$users];

        return response()->json($response, 201);
    });
});

Route::group(['middleware' => 'web'], function () {

    Route::H('countries', '\App\Http\Controllers\API\CountryController');

    Route::delete('/destroySelection', [\App\Http\Controllers\API\CountryController::class, 'destroySelection'])->name("destroySelection");

    Route::get('/export', [\App\Http\Controllers\API\CountryController::class, 'exportCsv'])->name("export");

    Route::get('/login/google', [\App\Http\Controllers\Api\AuthController::class, 'redirectToProvider']);
    Route::get('/login/google/callback', [\App\Http\Controllers\API\AuthController::class, 'handleProviderCallback']);
    Route::get('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);


});


