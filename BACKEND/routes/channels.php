<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/broadcasting/auth', ['middleware' => ['jwt.auth'], function (Request $request) {
    generateLogs('info', 'Broadcasting auth request: ' . json_encode(['request' => $request->all()]));
    return Broadcast::auth($request);
}]);

Broadcast::channel('{domain}-{id}-call-events', function ($user, $domain, $id) {
    return true;
});

Broadcast::channel('{domain}-{id}-agent-status', function ($user, $domain, $id) {
    return true;
});

Broadcast::channel('{domain}-{id}-customer-chat', function ($user, $domain, $id) {
    generateLogs('info', 'User trying to access the channel CUSTOMER CHAT: ' . json_encode(['user' => $user]));
    return true;
});

Broadcast::channel('{domain}-customer-chat-request', function ($user, $domain) {
    generateLogs('info', 'User trying to access the channel CUSTOMER CHAT REQUEST: ' . json_encode(['user' => $user]));
    return true;
});

Broadcast::channel('{domain}-{id}-agent-chat', function ($user, $domain, $id) {
    generateLogs('info', 'User trying to access the channel AGENT CHAT: ' . json_encode(['user' => $user]));
    return true;
});

Broadcast::channel('{domain}-{id}-force-logout', function ($user, $domain, $id) {
    generateLogs('info', 'Admin trying to FORCE LOGOUT the Agent: ' . json_encode(['user' => $user]));
    return (string) $user->id === (string) $id;
});

Broadcast::channel('{domain}-connect-customer', function ($user) {
    generateLogs('info', 'User trying to access the channel CONNECT CUSTOMER: ' . json_encode(['user' => $user]));
    return true;
});

Broadcast::channel('{domain}-customer_{id}-custom-chatbot', function ($user, $domain) {
    generateLogs('info', 'User trying to access the channel CUSTOM CHATBOT: ' . json_encode(['user' => $user]));
    return true;
});

Broadcast::channel('{domain}-agent_{id}-custom-chatbot-message', function ($user) {
    generateLogs('info', 'User trying to access the channel CUSTOM CHATBOT MESSAGE: ' . json_encode(['user' => $user]));
    return true;
});

// Broadcast::channel('{domain}-{id}-conference-events-direct', function ($user, $domain, $id) {
//     generateLogs('info', 'User trying to access the channel CONFERENCE EVENTS: ' . json_encode(['user' => $user]));
//     // return (int) $user->id === (int) $id;
//     return true;
// });

Broadcast::channel('{domain}-{id}-conference-events', function ($user, $domain, $id) {
    generateLogs('info', 'User trying to access the channel BASE CONFERENCE EVENTS: ' . json_encode(['user' => $user]));
    // return (int) $user->id === (int) $id;
    return true;
});

Broadcast::channel('{domain}-group_{groupId}-chat', function ($user, $domain, $groupId) {
    return \App\Models\GroupParticipant::where('group_id', $groupId)
        ->where('user_id', $user->id)
        ->whereNull('left_at')
        ->exists();
});
