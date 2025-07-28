<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            ActionSeeder::class,
            ApiConfigurationTypeSeeder::class,
            Chat360ApiConfigurationSeeder::class,
            MailTemplateSeeder::class,
            ModuleSeeder::class,
            SmsTemplateSeeder::class,
            SubModuleSeeder::class,
            TenantPrioritySeeder::class,
            TenantModulesSeeder::class,
            TenantTicketTypeSeeder::class,
            TenantSupportTicketStatusSeeder::class,
            EvaluationQuestionUsageTypesSeeder::class,
            PermissionSeeder::class,
         ]);
    }
}

