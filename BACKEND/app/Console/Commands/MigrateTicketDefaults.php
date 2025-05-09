<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;
use App\Models\Supportticket;
use App\Models\Priority;
use App\Models\TicketType;

class MigrateTicketDefaults extends Command
{
    protected $signature = 'tickets:migrate-defaults';

    protected $description = 'Map default_priority and default_types to priority_id and ticket_type_id';

    public function handle()
    {
        $this->info('Starting migration of support_tickets default values...');

        Tenant::where("domain_name","mk")->orderBy('id')  // Ensures consistent ordering by the ID column
            ->chunkById(100, function ($tenants) {
                foreach ($tenants as $tenant) {
                    $newConnectionName = str_replace(".", "_", $tenant->domain_name);
                    $databaseName = str_replace(".", "_", $tenant->domain_name);
                    establishDatabaseConnect($newConnectionName, $databaseName);
                    DB::reconnect($newConnectionName);
                    $supportTickets = Supportticket::on($newConnectionName)
                        ->select('id', 'default_priority', 'default_type')
                        ->get();
                    if ($supportTickets->count()) {
                        foreach ($supportTickets as $ticket) {
                            $priorityId = Priority::on($newConnectionName)->where('name', $ticket->default_priority)->value('id');
                            $ticketTypeId = TicketType::on($newConnectionName)->where('type', $ticket->default_type)->value('id');

                            // Log if not found
                            if (!$priorityId || !$ticketTypeId) {
                                $this->warn("  [Skipped] Ticket ID {$ticket->id} — Could not map priority/type");
                                continue;
                            }

                            $ticket->default_priority = $priorityId;
                            $ticket->default_type = $ticketTypeId;
                            $ticket->save();
                        }
                    }    
                }
            });
        $this->info('Migration completed.');
    }
}
