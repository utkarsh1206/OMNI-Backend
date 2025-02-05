<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class Role_ID extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('role_ids')->truncate();

        DB::table('role_ids')->insert([
            [
                'role_name' => 'superAdmin',
                'role_id' => '0',
            ],
            [
                'role_name' => 'tenantAdmin',
                'role_id' => '1',
            ],
            [
                'role_name' => 'manager',
                'role_id' => '2',
            ],
            [
                'role_name' => 'agent',
                'role_id' => '3',
            ],
        ]);
    }
}
