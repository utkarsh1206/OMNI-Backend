<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RoleSeeder::class,
            CronExpressionsSeeder::class,
            CronCommandsSeeder::class,
            ModuleSeeder::class,
            SubModuleSeeder::class,
            BusinessTypesSeeder::class,
            IndustryTypesSeeder::class,
            MenuPermissionsSeeder::class,
            NamePrefixesSeeder::class,
            OccupationsSeeder::class,
            AdminsSeeder::class,
            CountriesSeeder::class,
            StatesSeeder::class,
            CitySeeder::class,
            PrioritySeeder::class,
            TicketTypesSeeder::class
        ]);
    }
}