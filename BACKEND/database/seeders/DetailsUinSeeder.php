<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DetailsUinSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('details')->select('id')->get()->each(function ($row) {
            DB::table('details')
                ->where('id', $row->id)
                ->update(['uin' => (string) Str::uuid()]);
        });
    }
}
