<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SmsProvider;

class SmsProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SmsProvider::create([
            'name' => 'twillio',
            'status' => "active",
        ]);
        SmsProvider::create([
            'name' => 'custom',
            'status' => "active",
        ]);
    }
}
