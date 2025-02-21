<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Nameprefixes;
use Carbon\Carbon;

class NamePrefixesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Nameprefixes::truncate();

        $namePrefixes = ['Mr', 'Miss', 'Mrs'];

        foreach ($namePrefixes as $prefix) {
            Nameprefixes::insert([
                'title' => $prefix,
                'status' => 'Active',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
