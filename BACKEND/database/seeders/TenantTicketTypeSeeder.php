<?php

namespace Database\Seeders;

use App\Models\TicketType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantTicketTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $masterStatus = DB::table('omnichannel.ticket_types')->get();

        foreach ($masterStatus as $status) {
            TicketType::updateOrCreate(
                ['type' => $status->type],
                [
                    'description' => $status->description,
                    'status' => $status->status,
                    'master_id' => $status->id,
                ]
            );
        }
    }
}
