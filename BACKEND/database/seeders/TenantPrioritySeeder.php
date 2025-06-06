<?php

namespace Database\Seeders;

use App\Models\Priority;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenantPrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $masterStatus = DB::table('omnichannel.priorities')->get();

        foreach ($masterStatus as $status) {
            Priority::updateOrCreate(
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
