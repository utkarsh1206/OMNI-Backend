<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ApiConfigurationType;

class ApiConfigurationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table before seeding
        ApiConfigurationType::truncate();

        ApiConfigurationType::create([
            'name' => 'Sms'
        ]);
        ApiConfigurationType::create([
            'name' => 'Api',
        ]);
        ApiConfigurationType::create([
            'name' => 'Chatbot'
        ]);
        
    }
}
