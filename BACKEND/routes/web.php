<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TestController;

Route::get('/test', [TestController::class, 'index']);

Route::get('/', function () {
    return view('welcome');
});


