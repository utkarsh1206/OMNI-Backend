<?php

namespace App\Observers;

use App\Models\TicketType;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TicketTypeObserver
{
    public function created(TicketType $ticketType)
    {
        $this->syncTicketTypes($ticketType, 'create');
    }

    public function updated(TicketType $ticketType)
    {
        $this->syncTicketTypes($ticketType, 'update');
    }

    public function deleted(TicketType $ticketType)
    {
        $this->syncTicketTypes($ticketType, 'delete');
    }

    private function syncTicketTypes(TicketType $ticketType, $action)
    {
        $user = Auth::user();
        if ($user && $user->role_id == 0) {

            if ($action !== 'delete') {
                $masterData = DB::table('ticket_types')->where('type', $ticketType->type)->first();
                if (!$masterData) {
                    generateLogs("error", "type not found in master table");
                    return;
                }
            }

            $details = DB::table('details')->get();

            foreach ($details as $detail) {
                $newConnectionName = $detail->db;
                $this->setupTenantConnection($newConnectionName, $detail->username, $detail->password);

                // Skip if table doesn't exist
                if (!Schema::connection($newConnectionName)->hasTable('ticket_types')) {
                    generateLogs("warning", "ticket_types table missing in tenant DB". json_encode([
                        'tenant_db' => $newConnectionName
                    ]));
                    continue;
                }

                try {
                    if ($action === 'create') {
                        $existingType = DB::connection($newConnectionName)
                            ->table('ticket_types')
                            ->where('type', $ticketType->type)
                            ->exists();

                        if (!$existingType) {
                            TicketType::on($newConnectionName)->create([
                                'type' => $ticketType->type,
                                'description' => $ticketType->description,
                                'status' => $ticketType->status,
                                'master_id' => $masterData->id,
                            ]);
                        }
                    } else if ($action === 'update') {
                        $existingType = DB::connection($newConnectionName)
                            ->table('ticket_types')
                            ->where('master_id', $ticketType->id)
                            ->first();

                        if ($existingType) {
                            // Only update if it exists in the tenant DB
                            DB::connection($newConnectionName)
                                ->table('ticket_types')
                                ->where('id', $existingType->id)
                                ->update([
                                    'type' => $ticketType->type,
                                    'description' => $ticketType->description,
                                    'status' => $ticketType->status,
                                ]);
                        } else {
                            generateLogs("warning", "ticket_types missing in tenant DB during update" . json_encode([
                                'tenant_db' => $newConnectionName,
                                'master_id' => $ticketType->id,
                                'type' => $ticketType->type,
                            ]));
                        }
                    } else if ($action === 'delete') {
                        $existingType = DB::connection($newConnectionName)
                            ->table('ticket_types')
                            ->where('master_id', $ticketType->id)
                            ->first();

                        if ($existingType) {
                            DB::connection($newConnectionName)
                                ->table('ticket_types')
                                ->where('id', $existingType->id)
                                ->delete();
                        } else {
                            generateLogs("warning", "ticket_types missing in tenant DB during delete" . json_encode([
                                'tenant_db' => $newConnectionName,
                                'master_id' => $ticketType->id,
                                'type' => $ticketType->type,
                            ]));
                        }
                    }
                } catch (Exception $e) {
                    generateLogs("warning", "Failed to sync ticket type to tenant" . json_encode([
                        'tenant_db' => $newConnectionName,
                        'type' => $ticketType->type,
                        'master_id' => $ticketType->id,
                    ]));
                    continue;
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
