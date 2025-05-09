<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TenantModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            // 'Users',
            // 'User Groups',
            'Support Tickets',
            'Accounts',
            'Contacts',
            // 'Departments',
            // 'Categories',
            // 'Incident Type',
            // 'Product',
            // 'Pause Code',
            // 'List',
            // 'Leads',
            'Ticket Report',
            'SLA Report',
            'Audit Report',
            'Email Logs',
            'Call Log Report',
            'User Activity Report',
            // 'Phones',
            // 'Live Agents',
            // 'Settings',
            'Agent Chat',
        ];

        $now = Carbon::now();

        foreach ($modules as $module) {
            DB::table('tenant_modules')->updateOrInsert(
                ['name' => Str::slug($module, '_')],
                [
                    'display_name' => $module,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
