<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MenuPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menu_permissions')->truncate();

        $timestamp = Carbon::now();

        DB::table('menu_permissions')->insert([
            [
                'module_name' => 'user',
                'view_name' => 'viewUsers',
                'roles' => 'add,edit,delete,view',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'user_group',
                'view_name' => 'viewUserGroups',
                'roles' => 'add,edit,delete,view',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'support_ticket',
                'view_name' => 'viewSupportTicket',
                'roles' => 'add,edit,delete,view',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
    }
}
