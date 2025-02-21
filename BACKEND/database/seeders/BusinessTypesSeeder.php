<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\BusinessType;

class BusinessTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BusinessType::truncate();

        $businessTypes = [
            ['title' => 'Analyst'],
            ['title' => 'Competitor'],
            ['title' => 'Customer'],
            ['title' => 'Integrator'],
            ['title' => 'Investor'],
            ['title' => 'Partner'],
            ['title' => 'Press'],
            ['title' => 'Prospect'],
            ['title' => 'Reseller'],
            ['title' => 'Other'],
            ['title' => 'Farmer', 'status' => 'Inactive'],
        ];

        // Insert data using the model
        foreach ($businessTypes as $type) {
            BusinessType::insert([
                'title' => $type['title'],
                'status' => 'Active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
