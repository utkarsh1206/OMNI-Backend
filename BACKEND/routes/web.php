<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatBootWebhookController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('chatboot-webhook', [ChatBootWebhookController::class, 'index']);

