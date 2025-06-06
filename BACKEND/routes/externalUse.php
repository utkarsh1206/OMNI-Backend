<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatBotWebhookController;
use App\Http\Controllers\Api\AuthTokenController;
use  App\Http\Middleware\AuthTokenMiddleware;
use App\Http\Controllers\Api\UserMessageController;

Route::get('generate-auth-token/{uinId}', [AuthTokenController::class, 'index']);

Route::post('chatbot-room-id/{uinId}', [ChatBotWebhookController::class, 'fetchMessageFromRoom'])->middleware(AuthTokenMiddleware::class);

Route::post('fetch-session-messages/{uinId}', [UserMessageController::class, 'fetchChatResponse'])->middleware(AuthTokenMiddleware::class);


