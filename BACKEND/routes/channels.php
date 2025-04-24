<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/broadcasting/auth', ['middleware' => ['jwt.auth'], function (Request $request) {
    // Log the incoming request
    generateLogs('info', 'Broadcasting auth request: ' . json_encode(['request' => $request->all()]));

    // Handle authentication and allow access to private channels
    return Broadcast::auth($request);
}]);

Broadcast::channel('dialer_{type}', function ($user, $type) {
    generateLogs('info', 'User trying to access the channel: ' . json_encode(['user' => $user]));
    return true;
});

Broadcast::channel('{domain}-{id}-call-events', function ($user, $id, $domain) {
    generateLogs('info', 'User trying to access the channel: ' . json_encode(['user' => $user]));
    return true;
});
