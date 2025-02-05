<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table before seeding
        Module::truncate();

        $module = [
            ['name' => "Profile"],
            ['name' => "Support Tickets"],
            ['name' => "Accounts"],
            ['name' => "Contacts"],
            ['name' => "Departments"],
            ['name' => "Categories"],
            ['name' => "Incident Type"],
            ['name' => "Call CDR Report"],
            ['name' => "Agent Activity Report"],
            ['name' => "Ticket Report"], 
            ['name' => "SLA Report"],
            ['name' => "Phones"],
            ['name' => "Dashboard"],
            ['name' => "Settings"],
            ['name' => "Email Settings"],
            ['name' => "Gmail"],
            ['name' => " Email Templates"],
            ['name' => "SLA Configurations"],
            ['name' => "CRON Configuration"],
            ['name' => "Login"],
            ['name' => "Tenants"],
            ['name' => "Roles"],
            ['name' => "Users"],
            ['name' => "User Groups"],
           
            
        ];
        foreach ($module as  $moduleInfo) {
            Module::create([
                'name' => $moduleInfo["name"],
            ]);
        }
    }
}
