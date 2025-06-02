<?php

namespace Database\Seeders;

use App\Models\SupportTicketStatus;
use Illuminate\Database\Seeder;

class SupportTicketStatusSeeder extends Seeder
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
            SupportTicketStatus::updateOrCreate(['name' => $status['name']], $status);
        }
    }
}
