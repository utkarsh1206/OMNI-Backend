<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            ActionSeeder::class,
            AdminsSeeder::class,
            BusinessTypesSeeder::class,
            CitySeeder::class,
            CountriesSeeder::class,
            CronCommandsSeeder::class,
            CronExpressionsSeeder::class,
            IndustryTypesSeeder::class,
            MenuPermissionsSeeder::class,
            ModuleSeeder::class,
            NamePrefixesSeeder::class,
            OccupationsSeeder::class,
            PrioritySeeder::class,
            RoleSeeder::class,
            StatesSeeder::class,
            SubModuleSeeder::class,
            SupportTicketStatusSeeder::class,
            TicketTypesSeeder::class,
            UsersSeeder::class,
        ]);
    }
}
