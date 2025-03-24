<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Models\Detail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class TenantLicenseUpdateController extends Controller
{
    public function updateLicense($id, Request $request)
    {
        $validated = $request->validate([
            'license_key' => 'required|string|max:255',
        ]);

        $licenseKey = $validated['license_key'];

        $tenant = Tenant::find($id);
        $tenant->license_key = $licenseKey;
        $tenant->save();

        $tenantDetail = Detail::where('domain', $tenant->domain_name)->first();

        if (!$tenantDetail) {
            return response()->json(['error' => 'Tenant details not found.'], 404);
        }

        $databaseName = $tenantDetail->db;
        $domianName = $tenantDetail->domain;

        $this->updateOrInsertLicense($databaseName, $domianName, $id, $licenseKey);

        return response()->json(['message' => 'License updated successfully.'], 200);
    }

    private function updateOrInsertLicense($databaseName, $domianName, $tenantId, $licenseKey)
    {
        // Dynamically switch to the tenant database
        DB::purge('omnichannel'); // Purge any existing database connection

        $newConnectionName = str_replace(".", "_", $domianName);
        establishDatabaseConnect($newConnectionName, $databaseName);

        $connection = DB::connection($newConnectionName);

        // Check if the license already exists for this tenant
        $license = $connection->table('licenses')->where('id', $tenantId)->first();

        if ($license) {
            // Update existing license
            $connection->table('licenses')->where('id', $tenantId)->update([
                'license_key' => $licenseKey,
                'updated_at' => now(),
            ]);
        } else {
            // Insert new license
            $connection->table('licenses')->insert([
                'id' => $tenantId,
                'license_key' => $licenseKey,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
