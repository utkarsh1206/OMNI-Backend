<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            MailTemplateSeeder::class,
            SmsProviderSeeder::class,
            ModuleSeeder::class,
            SubModuleSeeder::class,
            SmsTemplateSeeder::class,
            ApiConfigurationTypeSeeder::class,
            PrioritySeeder::class
         ]);
    }
}

