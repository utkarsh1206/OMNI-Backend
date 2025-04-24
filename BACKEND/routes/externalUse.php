<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatBotWebhookController;
use App\Http\Controllers\Api\AuthTokenController;
use  App\Http\Middleware\AuthTokenMiddleware;

Route::get('generate-auth-token', [AuthTokenController::class, 'index']);

Route::post('chatbot-room-id', [ChatBotWebhookController::class, 'checkAuthWebhook'])->middleware([AuthTokenMiddleware::class]);

Route::any('chatbot-webhook', [ChatBotWebhookController::class, 'index']);


