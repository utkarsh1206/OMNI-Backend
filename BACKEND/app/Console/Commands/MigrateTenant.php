<?php

// namespace App\Console\Commands;

// use Illuminate\Console\Command;
// use Illuminate\Support\Facades\Config;
// use Illuminate\Support\Facades\DB;
// use App\Models\Detail;

// class MigrateTenant extends Command
// {
//     // Adding the optional path argument
//     protected $signature = 'migrate:tenant {domain} {--path=}';
//     protected $description = 'Run migrations for a specific tenant database based on the domain.';

//     public function handle()
//     {
//         $domain = $this->argument('domain');
//         $path = $this->option('path');  // Get the migration path option

//         // Fetch tenant details from the database
//         $details = Detail::where('domain', $domain)->first();

//         if (!$details) {
//             $this->error('No tenant found for this domain.');
//             return 1; // exit with error
//         }

//         // Set the dynamic database connection
//         Config::set('database.connections.dynamic', [
//             'driver' => 'mysql',
//             'host' => '127.0.0.1',
//             'database' => $details->db,
//             'username' => $details->username,
//             'password' => $details->password,
//             'charset' => 'utf8mb4',
//             'collation' => 'utf8mb4_unicode_ci',
//             'prefix' => '',
//             'strict' => true,
//             'engine' => null,
//         ]);

//         Config::set('database.default', 'dynamic');

//         // Run migration with or without path
//         if ($path) {
//             $this->call('migrate', [
//                 '--database' => 'dynamic',
//                 '--path' => $path,
//             ]);
//         } else {
//             $this->call('migrate', [
//                 '--database' => 'dynamic',
//             ]);
//         }

//         $this->info('Migrations have been run for domain: ' . $domain);
//         return 0; // exit with success
//     }
// }

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\Detail;
class MigrateTenant extends Command
{
    // Adding the optional path argument
    protected $signature = 'migrate:tenant {domain} {--path=} {--force}';
    protected $description = 'Run migrations for a specific tenant database based on the domain.';
    
    public function handle()
    {
        $domain = $this->argument('domain');
        $path = $this->option('path');  // Get the migration path option
        $force = $this->option('force');
        // Fetch tenant details from the database
        $details = Detail::where('domain', $domain)->first();
        if (!$details) {
            $this->error('No tenant found for this domain.');
            return 1; // exit with error
        }
        // Set the dynamic database connection

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
        // Run migration with or without path

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

        return 0; // exit with success
    }
}
