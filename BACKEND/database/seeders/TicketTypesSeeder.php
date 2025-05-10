<?php

namespace Database\Seeders;

use App\Models\TicketType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table before seeding
        // TicketType::truncate();

        $types = [
            ['type' => 'Helps', 'description' => 'Help related tickets', 'status' => 'active'],
            ['type' => 'Other', 'description' => 'Other category tickets', 'status' => 'active'],
            ['type' => 'Feature Request', 'description' => 'Feature request tickets', 'status' => 'active'],
            ['type' => 'Questions', 'description' => 'Question related tickets', 'status' => 'active'],
            ['type' => 'Incident', 'description' => 'Incident tickets', 'status' => 'active'],
            ['type' => 'Problems', 'description' => 'Problem related tickets', 'status' => 'active'],
        ];

        foreach ($types as $type) {
            TicketType::create($type);
        }
    }
}
