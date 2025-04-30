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
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'user_group',
                'view_name' => 'viewUserGroups',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'support_ticket',
                'view_name' => 'viewSupportTicket',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'accounts',
                'view_name' => 'viewAccounts',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'contacts',
                'view_name' => 'viewContacts',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'departments',
                'view_name' => 'viewDepartments',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'categories',
                'view_name' => 'viewCategories',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'incident_types',
                'view_name' => 'viewIncidentType',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'products',
                'view_name' => 'viewProducts',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'pause_codes',
                'view_name' => 'viewPauseCode',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'lists',
                'view_name' => 'viewLists',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'leads',
                'view_name' => 'viewLeads',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'ticket_reports',
                'view_name' => 'viewTicketReports',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'sla_reports',
                'view_name' => 'viewSlaReports',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'audit_reports',
                'view_name' => 'viewAuditReports',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'email_logs',
                'view_name' => 'viewEmailLogs',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'call_log_reports',
                'view_name' => 'viewCallLogReports',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'user_activity_reports',
                'view_name' => 'viewUserActivityReports',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'phones',
                'view_name' => 'viewPhones',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'live_agents',
                'view_name' => 'viewLiveAgents',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'settings',
                'view_name' => 'viewSettings',
                'roles' => 'allowed',
                'section' => 'module',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'email',
                'view_name' => 'sendEmail',
                'roles' => 'allowed',
                'section' => 'service',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'sms',
                'view_name' => 'sendSms',
                'roles' => 'allowed',
                'section' => 'service',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'chat',
                'view_name' => 'sendChat',
                'roles' => 'allowed',
                'section' => 'service',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'agent_chat',
                'view_name' => 'sendAgentChat',
                'roles' => 'allowed',
                'section' => 'service',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'thirdparty_integration',
                'view_name' => 'thirdpartyIntegration',
                'roles' => 'allowed',
                'section' => 'service',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
            [
                'module_name' => 'chatbot',
                'view_name' => 'chatBot',
                'roles' => 'allowed',
                'section' => 'service',
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ],
        ]);
    }
}
