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
            ['name' => "Email Templates"],
            ['name' => "SLA Configurations"],
            ['name' => "CRON Configuration"],
            ['name' => "Login"],
            ['name' => "Tenants"],
            ['name' => "Roles"],
            ['name' => "Users"],
            ['name' => "User Groups"],
            ['name' => "Chatbot Templates"],
            ['name' => "Neox Configurations"],
            ['name' => "Dynamic Form"],
            ['name' => "API Configurations"],
            ['name' => "Import Utility"],
            ['name' => "Export Utility"],
            ['name' => "Priority Settings"],
            ['name' => "Ticket Type Settings"],
            ['name' => "Satus Settings"],
            ['name' => "Evaluation"],
            ['name' => "Audit Report"],
            ['name' => "Email Logs"],
            ['name' => "Call Log Report"],
            ['name' => "User Activity Report"],
            ['name' => "Pause Code"],
            ['name' => "List"],
            ['name' => "Lead Upload"],
            ['name' => "Lead"],
            ['name' => "Change Password"],
            ['name' => "Live Agents"],
            ['name' => "Live Calls"],
            ['name' => "Logout"],
            ['name' => "Product"],

        ];
        foreach ($module as  $moduleInfo) {
            Module::create([
                'name' => $moduleInfo["name"],
            ]);
        }
    }
}
