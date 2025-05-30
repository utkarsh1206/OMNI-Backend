<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;
use Illuminate\Support\Facades\DB;

class TenantStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $masterStatus = DB::table('omnichannel.statuses')->get();

        foreach ($masterStatus as $status) {
            Status::updateOrCreate(
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
