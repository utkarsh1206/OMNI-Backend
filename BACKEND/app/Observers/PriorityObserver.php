<?php

namespace App\Observers;

use App\Models\Detail;
use App\Models\Priority;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PriorityObserver
{
    public function created(Priority $priority)
    {
        $this->syncPriorities($priority, 'create');
    }

    public function updated(Priority $priority)
    {
        $this->syncPriorities($priority, 'update');
    }

    public function deleted(Priority $priority)
    {
        $this->syncPriorities($priority, 'delete');
    }

    private function syncPriorities(Priority $priority, $action)
    {
        $user = Auth::user();
        if ($user && $user->role_id == 0) {

            if ($action !== 'delete') {
                $masterData = DB::table('priorities')->where('name', $priority->name)->first();
                if (!$masterData) {
                    generateLogs("error", "Priority not found in master table", $priority->name);
                    return;
                }
            }

            $details = DB::table('details')->get();

            foreach ($details as $detail) {
                $newConnectionName = $detail->db;
                $this->setupTenantConnection($newConnectionName, $detail->username, $detail->password);

                if (Schema::connection($newConnectionName)->hasTable('priorities')) {
                    if ($action === 'create') {
                        $existingPriority = DB::connection($newConnectionName)
                            ->table('priorities')
                            ->where('name', $priority->name)
                            ->exists();

                        if (!$existingPriority) {
                            Priority::on($newConnectionName)->create([
                                'name' => $priority->name,
                                'description' => $priority->description,
                                'status' => $priority->status,
                                'master_id' => $masterData->id,
                            ]);
                        }
                    } else if ($action === 'update') {
                        $existingPriority = DB::connection($newConnectionName)
                            ->table('priorities')
                            ->where('master_id', $priority->id)
                            ->first();

                        // Check if $existingPriority is an object (or null)
                        if ($existingPriority) {
                            // Only update if it exists in the tenant DB
                            DB::connection($newConnectionName)
                                ->table('priorities')
                                ->where('id', $existingPriority->id)
                                ->update([
                                    'name' => $priority->name,
                                    'description' => $priority->description,
                                    'status' => $priority->status,
                                ]);
                        } else {
                            generateLogs("warning", "Priority missing in tenant DB during update", [
                                'tenant_db' => $newConnectionName,
                                'master_id' => $priority->id,
                                'priority_name' => $priority->name,
                            ]);
                        }
                    } else if ($action === 'delete') {
                        // Delete logic
                        $existingPriority = DB::connection($newConnectionName)
                            ->table('priorities')
                            ->where('master_id', $priority->id)
                            ->first();

                        if ($existingPriority) {
                            DB::connection($newConnectionName)
                                ->table('priorities')
                                ->where('id', $existingPriority->id)
                                ->delete();
                        } else {
                            generateLogs("warning", "Priority missing in tenant DB during delete", [
                                'tenant_db' => $newConnectionName,
                                'master_id' => $priority->id,
                                'priority_name' => $priority->name,
                            ]);
                        }
                    }
                } else {
                    generateLogs("warning", "Priorities table missing in tenant DB", [
                        'tenant_db' => $newConnectionName,
                    ]);
                }
            }
        }
    }

    private function setupTenantConnection($connection, $username, $password)
    {
        Config::set("database.connections.$connection", [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => $connection,
            'username'  => env('DB_USERNAME', $username),
            'password'  => env('DB_PASSWORD', $password),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
            'engine'    => null,
        ]);
    }
}
