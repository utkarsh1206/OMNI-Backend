<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ChatBotWebhookController;
use App\Http\Controllers\Api\AuthTokenController;
use  App\Http\Middleware\AuthTokenMiddleware;
use App\Http\Controllers\Api\UserMessageController;
use App\Http\Controllers\Api\WebhookCallController;
use App\Http\Controllers\Api\UserController;

Route::get('generate-auth-token/{uinId}', [AuthTokenController::class, 'index']);

// Route::post('chatbot-room-id/{uinId}', [ChatBotWebhookController::class, 'fetchMessageFromRoom'])->middleware(AuthTokenMiddleware::class);

// Route::post('fetch-session-messages/{uinId}', [UserMessageController::class, 'fetchChatResponse'])->middleware(AuthTokenMiddleware::class);

// Route::post('call-initiate', [WebhookCallController::class, 'callInitiated']);  
// Route::post('call-connected', [WebhookCallController::class, 'callConnected']);
// Route::post('call-hangup', [WebhookCallController::class, 'callHangup']); 
// Route::post('call-history', [WebhookCallController::class, 'callHistory']);

Route::middleware(AuthTokenMiddleware::class)->group(function () {
    Route::post('chatbot-room-id/{uinId}', [ChatBotWebhookController::class, 'fetchMessageFromRoom']);
    Route::post('fetch-session-messages/{uinId}', [UserMessageController::class, 'fetchChatResponse']);
    Route::post('call-initiate', [WebhookCallController::class, 'callInitiated']);  
    Route::post('call-connected', [WebhookCallController::class, 'callConnected']);
    Route::post('call-hangup', [WebhookCallController::class, 'callHangup']); 
    Route::post('call-history', [WebhookCallController::class, 'callHistory']);
    Route::post('dc-user-logout/{uinId}', [UserController::class, 'dcUserLogout']);
});