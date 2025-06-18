<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/broadcasting/auth', ['middleware' => ['jwt.auth'], function (Request $request) {
    generateLogs('info', 'Broadcasting auth request: ' . json_encode(['request' => $request->all()]));

    return Broadcast::auth($request);
}]);

// Broadcast::channel('dialer_{type}', function ($user, $type) {
//     generateLogs('info', 'User trying to access the channel: ' . json_encode(['user' => $user]));
//     return true;
// });

Broadcast::channel('{domain}-{id}-call-events', function ($user, $domain, $id) {
    return true;
});

Broadcast::channel('{domain}-{id}-agent-status', function ($user, $domain, $id) {
    return true;
});

Broadcast::channel('{domain}-{id}-customer-chat', function ($user, $domain, $id) {
    generateLogs('info', 'User trying to access the channel: ' . json_encode(['user' => $user]));
    return true;
});

Broadcast::channel('{domain}-customer-chat-request', function ($user, $domain) {
    generateLogs('info', 'User trying to access the channel: ' . json_encode(['user' => $user]));
    return true;
});

Broadcast::channel('{domain}-{id}-agent-chat', function ($user, $domain, $id) {
    generateLogs('info', 'User trying to access the channel: ' . json_encode(['user' => $user]));
    return true;
});

Broadcast::channel('{domain}-{id}-force-logout', function ($user, $domain, $id) {
    generateLogs('info', 'Admin trying to force logout the Agent: ' . json_encode(['user' => $user]));
    // return true;
    return (string) $user->id === (string) $id;
});
