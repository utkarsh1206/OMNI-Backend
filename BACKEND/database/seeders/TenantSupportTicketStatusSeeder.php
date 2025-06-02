<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupportTicketStatus;
use Illuminate\Support\Facades\DB;

class TenantSupportTicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $masterStatus = DB::table('omnichannel.support_ticket_statuses')->get();

        foreach ($masterStatus as $status) {
            SupportTicketStatus::updateOrCreate(
                ['name' => $status->name],
                [
                    'description' => $status->description,
                    'status' => $status->status,
                    'master_id' => $status->id,
                ]
            );
        }
    }
}
