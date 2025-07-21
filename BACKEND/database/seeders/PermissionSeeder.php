<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Load JSON config from storage
        $jsonPath = storage_path('app/private/permissions_map.json');
        if (!file_exists($jsonPath)) {
            throw new \Exception("Missing permissions_map.json at $jsonPath");
        }

        $mapping = json_decode(file_get_contents($jsonPath), true);

        $actionIds = DB::table('actions')->pluck('id', 'name');
        $moduleIds = DB::table('tenant_modules')->pluck('id', 'name'); 

        $now = Carbon::now();
        $permissions = [];

        foreach ($mapping as $module => $actions) {
            if (!isset($moduleIds[$module])) {
                echo "Skipping: module $module not found in DB\n";
                continue;
            }

            foreach ($actions as $action) {
                if (!isset($actionIds[$action])) {
                    echo "Skipping: action $action not found in DB\n";
                    continue;
                }

                $permissions[] = [
                    'module_id' => $moduleIds[$module],
                    'action_id' => $actionIds[$action],
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        DB::table('permissions')->insert($permissions);
    }
}
