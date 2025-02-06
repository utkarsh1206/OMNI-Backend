<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
           // Role_ID::class,
            //CronExpressionsSeeder::class,
            //CronCommandsSeeder::class,
            MailTemplateSeeder::class,
        ]);
    }n
}