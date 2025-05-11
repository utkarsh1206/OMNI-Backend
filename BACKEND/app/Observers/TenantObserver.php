<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Models\Detail;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\Users;
use Illuminate\Support\Str;

class TenantObserver
{

    public function created(Tenant $tenants): void
    {
        $lastInsertedId = $tenants->id;

        $databaseName = convertToLower(Str::replace(".", "_", request()->domain_name));

        $tenantDetails = [
            'db' => $databaseName,
            'domain' => request()->domain_name,
            'uin' => (string) Str::uuid(),
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', 'Sterlite@2025'),
        ];

        Detail::create($tenantDetails);

        // DB::table('details')->insert($details_array);

        // $newConnectionName = str_replace(".", "_", request()->domain_name);

        DB::statement("CREATE DATABASE $databaseName");

        Config::set("database.connections.$databaseName", [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => $databaseName,
            'username'  => env('DB_USERNAME', 'root'),
            'password'  => env('DB_PASSWORD', 'Sterlite@2025'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ]);

        // Artisan::call('migrate', [
        //     '--database' => $databaseName,
        //     '--path'     => '/database/migrations/user', 
        //     '--force'    => true,
        // ]);

        Users::on($databaseName)->create([
            'tenant_id'  => $lastInsertedId,
            'type' => 0,
            'first_name' => request()->first_name,
            'last_name'  => request()->last_name,
            'password'   => bcrypt(request()->password),
            'email'      => request()->email,
            'username'   => request()->username,
            'domain'     => request()->domain_url,
            'mobile'     => request()->mobile,
            'status' => 'Active',
            'role_id' => 1
        ]);

        // Artisan::call('db:seed', [
        //     '--database' => $databaseName,
        //     '--class' => 'TenantSeeder',
        //     '--force' => true, 
        // ]);
    }
}
