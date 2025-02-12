<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\CronCommand;
use Carbon\Carbon;

class CronCommandsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cronCommands = [
            ['command_signature' => 'app:support-ticket-close', 'type' => 'cron'],
	        ['command_signature' => 'app:sla-email-notification', 'type' => 'other'],
	    
		['command_signature' => 'app:existing-tenant-seeder', 'type' => 'other'],
	];
        foreach ($cronCommands as $cronCommand) {
            CronCommand::updateOrCreate(
                ['command_signature' =>  $cronCommand['command_signature'],],  // Search by command_signature
                [
                    'command_signature' =>  $cronCommand['command_signature'],
                    'type' => $cronCommand['type'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }

    }

}