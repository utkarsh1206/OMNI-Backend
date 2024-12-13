<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Admins;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LicenseUpdateController extends Controller
{
    public function updateLicense(Request $request)
    {
        $validated = $request->validate([
            'license_key' => 'required|string',
        ]);

        $userId = Auth::user()->id;

        $license = Admins::where('id', $userId)->first();

        $license->license_key = $request->input('license_key');
        $license->save();

        // Log the action with user ID and license key
        Log::debug('License updated', [
            'user_id' => $userId,
            'license_key' => $license->license_key
        ]);

        // Response indicating update
        return response()->json([
            'code' => 0,
            'message' => 'License updated successfully',
        ], 200);
    }
}
