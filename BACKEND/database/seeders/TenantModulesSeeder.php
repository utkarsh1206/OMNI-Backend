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
            'accounts',
            'agent chat',
            'audit reports',
            'call log reports',
            'calling',
            'categories',
            'chat',
            'chatbot',
            'contacts',
            'departments',
            'email',
            'email logs',
            'evaluation',
            'incident type',
            'leads',
            'list',
            'live agents',
            'pause codes',
            'phones',
            'product',
            'settings',
            'sla reports',
            'sms',
            'support tickets',
            'thirdparty integration',
            'ticket reports',
            'user',
            'user activity reports',
            'user Groups',
            'user roles',
        ];

        $nonModules = [
            'agent chat',
            'chat',
            'chatbot',
            'calling',
            'email',
            'evaluation',
            'live_agents',
            'sms',
            'thirdparty integration',
        ];

        $now = Carbon::now();

        // DB::table('tenant_modules')->truncate();

        foreach ($modules as $module) {
            $isModule = in_array($module, $nonModules) ? '0' : '1';

            DB::table('tenant_modules')->updateOrInsert(
                ['name' => Str::slug($module, '_')],
                [
                    'display_name' => ucwords($module),
                    'is_module' => $isModule,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}
