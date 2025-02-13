<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Occupations;
use Carbon\Carbon;

class OccupationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Occupations::truncate();

        $occupations = [
            ['title' => 'Salaried'],
            ['title' => 'Student'],
            ['title' => 'Retired'],
            ['title' => 'Business Owner'],
            ['title' => 'Self-Employed Professional'],
            ['title' => 'Independent Worker'],
            ['title' => 'Farmer'],
            ['title' => 'Shopkeeper New'],
        ];

        foreach ($occupations as $occupation) {
            Occupations::insert([
                'title' => $occupation['title'],
                'user_id' => 0,
                'status' => 'Active',
                'is_delete' => 'No',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
