<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ReportDownloadController extends Controller
{
    public function download(Request $request)
    {
        $filename = $request->query('filename');

        if (!$filename || !preg_match('/^[A-Za-z0-9_\-\.]+$/', $filename)) {
            abort(403);
        }

        $path = 'exports/' . $filename;

        if (!Storage::disk('public')->exists($path)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return response()->download(
            storage_path('app/public/' . $path)
        );
    }
}
