<?php

namespace App\Console\Commands;

use App\Models\LiveAgents;
use Illuminate\Console\Command;

class CleanEndOfDayData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-end-of-day-data {uin?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $uin = $this->argument('uin');

        generateScheduleLogs('info', "Cleaning end of day data for tenant $uin started.");

        getDbConnection($uin, true);
        LiveAgents::truncate();
        generateScheduleLogs('info', "Cleaning end of day data for tenant $uin completed.");
    }
}
