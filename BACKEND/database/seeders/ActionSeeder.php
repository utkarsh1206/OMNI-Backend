<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            'create',
            'delete',
            'download',
            'edit',
            'enable',
            'view',
        ];

        $now = Carbon::now();

        foreach ($modules as $module) {
            DB::table('actions')->updateOrInsert(
                ['name' => $module],
                [
                    'display_name' => ucfirst($module),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
