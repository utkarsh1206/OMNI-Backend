<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return 'Backend is ready';
});

Route::get('/download/{filename}', function ($filename) {
    $path = 'attachment/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->download($path);
});
