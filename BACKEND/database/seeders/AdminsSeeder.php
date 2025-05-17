<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('admins')->truncate();

        DB::table('admins')->insert([
            // 'edit_id' => 'd9a9193805dc8832cadf31791f51949b',
            'username' => 'superadmin',
            'first_name' => 'Super',
            'last_name' => 'Admin',
            'email' => 'superadmin@neox.com',
            'mobile' => 1234567890,
            'password' => Hash::make('superadmin'),
            'otp' => null,
            'admin_type' => 'Super',
            'status' => 'Active',
            'license_key' => '',
            'license_updated_date' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);
    }
}
