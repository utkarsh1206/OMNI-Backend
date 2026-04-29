<?php

namespace App\Jobs;

use App\Models\Tenant;
use App\Models\Users;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ProvisionTenantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tenant;
    protected $databaseName;
    protected $requestData;

    /**
     * Create a new job instance.
     */
    public function __construct(Tenant $tenant, string $databaseName, array $requestData)
    {
        $this->tenant = $tenant;
        $this->databaseName = $databaseName;
        $this->requestData = $requestData;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Create the Database
        DB::statement("CREATE DATABASE {$this->databaseName}");

        // Configure the dynamic connection for the migration/seeding
        Config::set("database.connections.{$this->databaseName}", [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => $this->databaseName,
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', 'Sterlite@2025'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ]);

        // Run Migrations
        Artisan::call('migrate', [
            '--database' => $this->databaseName,
            '--path'     => '/database/migrations/user',
            '--force'    => true,
        ]);

        // Create Tenant Admin User
        Users::on($this->databaseName)->create([
            'tenant_id'  => $this->tenant->id,
            'type'       => 0,
            'first_name' => $this->requestData['first_name'],
            'last_name'  => $this->requestData['last_name'],
            'password'   => bcrypt($this->requestData['password']),
            'email'      => $this->requestData['email'],
            'username'   => $this->requestData['username'],
            'domain'     => $this->requestData['domain_name'],
            'mobile'     => $this->requestData['mobile'],
            'status'     => 'Active',
            'role_id'    => 1
        ]);

        // Run Seeders
        Artisan::call('db:seed', [
            '--database' => $this->databaseName,
            '--class'    => 'TenantSeeder',
            '--force'    => true,
        ]);
    }
}
