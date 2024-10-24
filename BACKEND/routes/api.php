<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CountriesController;
use App\Http\Controllers\Api\TestController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\TenantController;
use App\Http\Controllers\Api\OccupationsController;
use App\Http\Controllers\Api\NameprefixesController;
use App\Http\Controllers\Api\StatesController;
use App\Http\Controllers\Api\CitiesController;
use App\Http\Controllers\Api\UsersController;
use App\Http\Controllers\Api\UsergroupController;
use App\Http\Controllers\Api\TenantroleController;
use App\Models\Tenantrole;

Route::post('login', [LoginController::class, 'login_action']);

Route::get('get_test', [TestController::class, 'index']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
});
//Route::get('login', [AuthController::class, 'login'])->name('login');
Route::get('countries', [CountriesController::class, 'index']);
Route::get('nameprefixes', [NameprefixesController::class, 'index']);
Route::get('occupations', [OccupationsController::class, 'index']);
Route::get('states', [StatesController::class, 'index']);
Route::get('cities', [CitiesController::class, 'index']);
Route::middleware('auth:api')->apiResource('tenants', TenantController::class);
Route::apiResource('users', UsersController::class);
Route::apiResource('usergroup', UsergroupController::class);
Route::apiResource('tenantrole', TenantroleController::class);
Route::get('tenantrole-list', [TenantroleController::class, 'list']);
Route::put('update-tenantpassword', [UsersController::class, 'updatepassword']);

#Route::middleware('auth:api')->apiResource('users', UsersController::class);

Route::middleware(['auth:api'])->group(function () {
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
    Route::post('logout', [AuthController::class, 'logout']);
});
