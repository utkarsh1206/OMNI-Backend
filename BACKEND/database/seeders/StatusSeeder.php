<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Open', 'description' => 'The item is currently open.',  'status' => 'active'],
            ['name' => 'Resolved', 'description' => 'The item has been resolved successfully.', 'status' => 'active'],
            ['name' => 'Closed', 'description' => 'The item has been closed.', 'status' => 'active'],
        ];

        foreach ($statuses as $status) {
            Status::updateOrCreate(['name' => $status['name']], $status);
        }
    }
}
