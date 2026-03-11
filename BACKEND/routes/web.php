<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Api\ReportDownloadController;

// Route::get('/', function () {
//     return 'Backend is ready';
// });

Route::get('/', [HomeController::class, 'index']);

Route::get('/download/{filename}', function ($filename) {
    $path = 'attachment/' . $filename;

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    return Storage::disk('public')->download($path);
});



Route::get('/exports/download', [ReportDownloadController::class, 'download'])
    ->name('exports.download')
    ->middleware('signed');