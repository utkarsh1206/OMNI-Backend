<?php

namespace App\Observers;

use App\Models\SupportTicketStatus;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SupportTicketStatusObserver
{
    public function created(SupportTicketStatus $ticket_status)
    {
        $this->syncStatuses($ticket_status, 'create');
    }

    public function updated(SupportTicketStatus $ticket_status)
    {
        $this->syncStatuses($ticket_status, 'update');
    }

    public function deleted(SupportTicketStatus $ticket_status)
    {
        $this->syncStatuses($ticket_status, 'delete');
    }

    private function syncStatuses(SupportTicketStatus $ticket_status, $action)
    {
        $user = Auth::user();
        if ($user && $user->role_id == 0) {

            $masterData = null;
            if ($action !== 'delete') {
                $masterData = DB::table('support_ticket_statuses')->where('name', $ticket_status->name)->first();
                if (!$masterData) {
                    generateLogs("error", "Status not found in master table");
                    return;
                }
            }

            DB::table('omnichannel.details')->orderBy('id')->chunk(50, function ($details) use ($ticket_status, $action, $masterData) {
                foreach ($details as $detail) {
                    $this->processTenant($detail, $ticket_status, $action, $masterData);
                }
            });
        }
    }


    private function processTenant($detail, SupportTicketStatus $ticket_status, string $action, $masterData = null)
    {
        $newConnectionName = str_replace(".", "_", $detail->domain);
        $databaseName = str_replace(".", "_", $detail->domain);
        establishDatabaseConnect($newConnectionName, $databaseName);

        if (!Schema::connection($databaseName)->hasTable('support_ticket_statuses')) {
            generateLogs("warning", "support_ticket_statuses table missing in tenant DB" . json_encode([
                'tenant_db' => $databaseName
            ]));
            return;
        }

        try {
            $tenantStatusQuery = DB::connection($databaseName)->table('support_ticket_statuses');

            if ($action === 'create') {
                $exists = $tenantStatusQuery->where('name', $ticket_status->name)->exists();
                if (!$exists) {
                    SupportTicketStatus::on($databaseName)->create([
                        'name' => $ticket_status->name,
                        'description' => $ticket_status->description,
                        'status' => $ticket_status->status,
                        'master_id' => $masterData->id,
                    ]);
                }
            } elseif ($action === 'update') {
                $existing = $tenantStatusQuery->where('master_id', $ticket_status->id)->first();
                if ($existing) {
                    $tenantStatusQuery->where('id', $existing->id)->update([
                        'name' => $ticket_status->name,
                        'description' => $ticket_status->description,
                        'status' => $ticket_status->status,
                    ]);
                } else {
                    generateLogs("warning", "Status missing in tenant DB during update" . json_encode([
                        'tenant_db' => $databaseName,
                        'master_id' => $ticket_status->id,
                        'status_name' => $ticket_status->name,
                    ]));
                }
            } elseif ($action === 'delete') {
                $existing = $tenantStatusQuery->where('master_id', $ticket_status->id)->first();
                if ($existing) {
                    $tenantStatusQuery->where('id', $existing->id)->delete();
                } else {
                    generateLogs("warning", "Status missing in tenant DB during delete" . json_encode([
                        'tenant_db' => $databaseName,
                        'master_id' => $ticket_status->id,
                        'status_name' => $ticket_status->name,
                    ]));
                }
            }
        } catch (Exception $e) {
            generateLogs("warning", "Failed to sync ticket type to tenant" . json_encode([
                'tenant_db' => $databaseName,
                'name' => $ticket_status->name,
                'master_id' => $ticket_status->id,
            ]));
            return;
        }
    }
}
