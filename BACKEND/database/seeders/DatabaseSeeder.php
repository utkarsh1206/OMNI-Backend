<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            Role_ID::class,
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
            CitiesSeeder::class,
        ]);
    }
}