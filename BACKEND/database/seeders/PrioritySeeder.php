<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Priority;

class PrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table before seeding
        Priority::truncate();

        $priorities = [
            ['name' => 'High', 'description' => 'Tasks that need immediate attention', 'status' => 'active'],
            ['name' => 'Medium', 'description' => 'Tasks that are important but not urgent', 'status' => 'active'],
            ['name' => 'Low', 'description' => 'Tasks with low urgency and priority', 'status' => 'active'],
        ];

        foreach ($priorities as $priority) {
            Priority::updateOrCreate(['name' => $priority['name']], $priority);
        }
    }
}
