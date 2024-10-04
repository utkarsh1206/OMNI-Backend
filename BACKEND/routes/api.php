<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\LoginController;
// use App\Http\Controllers\DecodeTokenController;

Route::post('login', [LoginController::class, 'login_action']);

// Route::post('decode-token', [DecodeTokenController::class,'decodeToken']);
// Route::middleware('auth:api')->post('/decode-token', [DecodeTokenController::class, 'decodeToken']);

Route::get('get_test', [TestController::class, 'index']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['prefix' => 'auth'], function ($router) {
    Route::post('login', [AuthController::class,'login']);
    Route::post('register',[AuthController::class,'register']);
});
Route::middleware(['auth:api'])->group(function(){
    Route::post('refresh', [AuthController::class,'refresh']);
    Route::post('me', [AuthController::class,'me']);
    Route::post('logout', [AuthController::class,'logout']);
});

