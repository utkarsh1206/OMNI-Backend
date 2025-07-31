<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use App\Models\Detail;
class MigrateTenant extends Command
{
    // Adding the optional path argument
    protected $signature = 'migrate:tenant {domain} {--path=} {--force}';
    protected $description = 'Run migrations for a specific tenant database based on the domain.';
    
    public function handle()
    {
        $domain = $this->argument('domain');
        $path = $this->option('path');  
        $force = $this->option('force');
     
        $details = Detail::where('domain', $domain)->first();
        if (!$details) {
            $this->error('No tenant found for this domain.');
            return 1; 
        }
       
        Config::set('database.connections.dynamic', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'database' => $details->db,
            'username' => $details->username,
            'password' => $details->password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]);
        Config::set('database.default', 'dynamic');
       
        $migrationOptions = [
            '--database' => 'dynamic',
        ];

        if ($path) {
            $migrationOptions['--path'] = $path;
        }

        if ($force) {
            $migrationOptions['--force'] = true;
        }

        $this->call('migrate', $migrationOptions);
        
        $this->info('Migrations have been run for domain: ' . $domain);

        return 0;
    }
}
