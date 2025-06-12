<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SubModule;
use App\Models\Module;

class SubModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Truncate the table before seeding
        SubModule::truncate();

        $subModule = [
            ['module_id' => $this->getModuleBySubmodule("Profile"), 'name' => 'GET api/my_profile'],
            ['module_id' =>  $this->getModuleBySubmodule("Support Tickets"), 'name' => 'GET api/support-ticket'],
            ['module_id' => $this->getModuleBySubmodule("Support Tickets"), 'name' => 'POST api/support-ticket'],
            ['module_id' => $this->getModuleBySubmodule("Support Tickets"), 'name' => 'PUT api/support-ticket/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Support Tickets"), 'name' => 'GET api/support-ticket/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Support Tickets"), 'name' => 'DELETE api/support-ticket/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Accounts"), 'name' => 'GET api/accounts'],
            ['module_id' => $this->getModuleBySubmodule("Accounts"), 'name' => 'POST api/accounts'],
            ['module_id' => $this->getModuleBySubmodule("Accounts"), 'name' => 'PUT api/accounts/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Accounts"), 'name' => 'DELETE api/accounts/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Contacts"), 'name' => 'GET api/contacts'],
            ['module_id' => $this->getModuleBySubmodule("Contacts"), 'name' => 'POST api/contacts'],
            ['module_id' => $this->getModuleBySubmodule("Contacts"), 'name' => 'PUT api/contacts/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Contacts"), 'name' => 'DELETE api/contacts/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Departments"), 'name' => 'GET api/departments'],
            ['module_id' => $this->getModuleBySubmodule("Departments"), 'name' => 'POST api/departments'],
            ['module_id' => $this->getModuleBySubmodule("Departments"), 'name' => 'PUT api/departments/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Departments"), 'name' => 'DELETE api/departments/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Categories"), 'name' => 'GET api/categories'],
            ['module_id' => $this->getModuleBySubmodule("Categories"), 'name' => 'POST api/categories'],
            ['module_id' => $this->getModuleBySubmodule("Categories"), 'name' => 'PUT api/categories/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Categories"), 'name' => 'DELETE api/categories/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Incident Type"), 'name' => 'GET api/incident-types'],
            ['module_id' => $this->getModuleBySubmodule("Incident Type"), 'name' => 'POST api/incident-types'],
            ['module_id' => $this->getModuleBySubmodule("Incident Type"), 'name' => 'PUT api/incident-types/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Incident Type"), 'name' => 'DELETE api/incident-types/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Call CDR Report"), 'name' => 'GET api/callCDR'],
            ['module_id' => $this->getModuleBySubmodule("Agent Activity Report"), 'name' => 'GET api/agent-activity'],
            ['module_id' => $this->getModuleBySubmodule("Ticket Report"), 'name' => 'GET api/ticketReport'],
            ['module_id' => $this->getModuleBySubmodule("SLA Report"), 'name' => 'GET api/slareport'],
            ['module_id' => $this->getModuleBySubmodule("Phones"), 'name' => 'GET api/phones'],
            ['module_id' => $this->getModuleBySubmodule("Phones"), 'name' => 'POST api/phones'],
            ['module_id' => $this->getModuleBySubmodule("Phones"), 'name' => 'PUT api/phones/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Phones"), 'name' => 'DELETE api/phones/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Gmail"), 'name' => 'GET api/email-configure'],
            ['module_id' => $this->getModuleBySubmodule("Gmail"), 'name' => 'POST api/email-configure'],
            ['module_id' => $this->getModuleBySubmodule("Gmail"), 'name' => 'PUT api/email-configure/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Gmail"), 'name' => 'DELETE api/email-configure/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Email Templates"), 'name' => 'GET api/mail-templates'],
            ['module_id' => $this->getModuleBySubmodule("Email Templates"), 'name' => 'POST api/mail-templates'],
            ['module_id' => $this->getModuleBySubmodule("Email Templates"), 'name' => 'PUT api/mail-templates/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Email Templates"), 'name' => 'DELETE api/mail-templates/{id}'],
            ['module_id' => $this->getModuleBySubmodule("SLA Configurations"), 'name' => 'GET api/sla-settings'],
            ['module_id' => $this->getModuleBySubmodule("SLA Configurations"), 'name' => 'POST api/sla-settings'],
            ['module_id' => $this->getModuleBySubmodule("SLA Configurations"), 'name' => 'PUT api/sla-settings/{id}'],
            ['module_id' => $this->getModuleBySubmodule("SLA Configurations"), 'name' => 'DELETE api/sla-settings/{id}'],
            ['module_id' => $this->getModuleBySubmodule("CRON Configuration"), 'name' => 'GET api/cron-configurations'],
            ['module_id' => $this->getModuleBySubmodule("CRON Configuration"), 'name' => 'POST api/cron-configurations'],
            ['module_id' => $this->getModuleBySubmodule("CRON Configuration"), 'name' => 'PUT api/cron-configurations/{id}'],
            ['module_id' => $this->getModuleBySubmodule("CRON Configuration"), 'name' => 'DELETE api/cron-configurations/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Login"), 'name' => 'POST api/login'],
            ['module_id' => $this->getModuleBySubmodule("Tenants"), 'name' => 'GET api/tenants'],
            ['module_id' => $this->getModuleBySubmodule("Tenants"), 'name' => 'POST api/tenants'],
            ['module_id' => $this->getModuleBySubmodule("Tenants"), 'name' => 'PUT api/tenants/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Tenants"), 'name' => 'DELETE api/tenants/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Roles"), 'name' => 'GET api/tenantrole'],
            ['module_id' => $this->getModuleBySubmodule("Roles"), 'name' => 'POST api/tenantrole'],
            ['module_id' => $this->getModuleBySubmodule("Roles"), 'name' => 'PUT api/tenantrole/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Roles"), 'name' => 'DELETE api/tenantrole/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Users"), 'name' => 'GET api/users'],
            ['module_id' => $this->getModuleBySubmodule("Users"), 'name' => 'POST api/users'],
            ['module_id' => $this->getModuleBySubmodule("Users"), 'name' => 'PUT api/users/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Users"), 'name' => 'DELETE api/users/{id}'],
            ['module_id' => $this->getModuleBySubmodule("User Groups"), 'name' => 'GET api/usergroup'],
            ['module_id' => $this->getModuleBySubmodule("User Groups"), 'name' => 'POST api/usergroup'],
            ['module_id' => $this->getModuleBySubmodule("User Groups"), 'name' => 'PUT api/usergroup/{id}'],
            ['module_id' => $this->getModuleBySubmodule("User Groups"), 'name' => 'DELETE api/usergroup/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Dashboard"), 'name' => 'GET api/dashboard/ticket_details'],
            ['module_id' => $this->getModuleBySubmodule("Roles"), 'name' => 'GET api/roles'],
            ['module_id' => $this->getModuleBySubmodule("Roles"), 'name' => 'POST api/roles'],
            ['module_id' => $this->getModuleBySubmodule("Roles"), 'name' => 'PUT api/roles/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Roles"), 'name' => 'GET api/roles/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Roles"), 'name' => 'DELETE api/roles/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Chatbot Templates"), 'name' => 'GET api/chatbots'],
            ['module_id' => $this->getModuleBySubmodule("Chatbot Templates"), 'name' => 'POST api/chatbots'],
            ['module_id' => $this->getModuleBySubmodule("Chatbot Templates"), 'name' => 'GET api/chatbots/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Chatbot Templates"), 'name' => 'PUT api/chatbots/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Chatbot Templates"), 'name' => 'DELETE api/chatbots/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Neox Configurations"), 'name' => 'GET api/neox-configurations'],
            ['module_id' => $this->getModuleBySubmodule("Neox Configurations"), 'name' => 'PUT api/neox-configurations/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Dynamic Form"), 'name' => 'GET api/dynamic-list-fields'],
            ['module_id' => $this->getModuleBySubmodule("Dynamic Form"), 'name' => 'POST api/dynamic-list-fields'],
            ['module_id' => $this->getModuleBySubmodule("Dynamic Form"), 'name' => 'PUT api/dynamic-list-fields/{id}'],
            ['module_id' => $this->getModuleBySubmodule("List"), 'name' => 'GET api/lists'],
            ['module_id' => $this->getModuleBySubmodule("List"), 'name' => 'GET api/lists/{id}'],
            ['module_id' => $this->getModuleBySubmodule("List"), 'name' => 'POST api/lists'],
            ['module_id' => $this->getModuleBySubmodule("List"), 'name' => 'PUT api/lists/{id}'],
            ['module_id' => $this->getModuleBySubmodule("List"), 'name' => 'DELETE api/lists/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Lead Upload"), 'name' => 'POST api/upload-lead-csv/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Lead"), 'name' => 'GET api/custom-leads/{id}'],
            ['module_id' => $this->getModuleBySubmodule("API Configurations"), 'name' => 'GET api/api-configurations'],
            ['module_id' => $this->getModuleBySubmodule("API Configurations"), 'name' => 'GET api/api-configurations/{id}'],
            ['module_id' => $this->getModuleBySubmodule("API Configurations"), 'name' => 'POST api/api-configurations'],
            ['module_id' => $this->getModuleBySubmodule("API Configurations"), 'name' => 'PUT api/api-configurations/{id}'],
            ['module_id' => $this->getModuleBySubmodule("API Configurations"), 'name' => 'DELETE api/api-configurations/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Import Utility"), 'name' => 'GET api/utility/import-listing'],
            ['module_id' => $this->getModuleBySubmodule("Import Utility"), 'name' => 'POST api/utility/import-database'],
            ['module_id' => $this->getModuleBySubmodule("Export Utility"), 'name' => 'GET api/utility/export-listing'],
            ['module_id' => $this->getModuleBySubmodule("Export Utility"), 'name' => 'GET api/utility/export-database'],
            ['module_id' => $this->getModuleBySubmodule("Priority Settings"), 'name' => 'GET api/priorities'],
            ['module_id' => $this->getModuleBySubmodule("Priority Settings"), 'name' => 'GET api/priorities/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Priority Settings"), 'name' => 'POST api/priorities'],
            ['module_id' => $this->getModuleBySubmodule("Priority Settings"), 'name' => 'PUT api/priorities/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Priority Settings"), 'name' => 'DELETE api/priorities/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Ticket Type Settings"), 'name' => 'GET api/ticket-types'],
            ['module_id' => $this->getModuleBySubmodule("Ticket Type Settings"), 'name' => 'GET api/ticket-types/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Ticket Type Settings"), 'name' => 'POST api/ticket-types'],
            ['module_id' => $this->getModuleBySubmodule("Ticket Type Settings"), 'name' => 'PUT api/ticket-types/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Ticket Type Settings"), 'name' => 'DELETE api/ticket-types/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Satus Settings"), 'name' => 'GET api/ticket-status'],
            ['module_id' => $this->getModuleBySubmodule("Satus Settings"), 'name' => 'GET api/ticket-status/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Satus Settings"), 'name' => 'POST api/ticket-status'],
            ['module_id' => $this->getModuleBySubmodule("Satus Settings"), 'name' => 'PUT api/ticket-status/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Satus Settings"), 'name' => 'DELETE api/ticket-status/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Evaluation"), 'name' => 'GET api/questions'],
            ['module_id' => $this->getModuleBySubmodule("Evaluation"), 'name' => 'GET api/questions/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Evaluation"), 'name' => 'POST api/questions'],
            ['module_id' => $this->getModuleBySubmodule("Evaluation"), 'name' => 'PUT api/questions/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Evaluation"), 'name' => 'DELETE api/questions/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Audit Report"), 'name' => 'GET api/audit-logs'],
            ['module_id' => $this->getModuleBySubmodule("Email Logs"), 'name' => 'GET api/email-logs'],
            ['module_id' => $this->getModuleBySubmodule("Call Log Report"), 'name' => 'GET api/call-log-report/list'],
            ['module_id' => $this->getModuleBySubmodule("Call Log Report"), 'name' => 'GET api/call-log-report/status-summary'],
            ['module_id' => $this->getModuleBySubmodule("Call Log Report"), 'name' => 'GET api/call-log-report/user-summary'],
            ['module_id' => $this->getModuleBySubmodule("Call Log Report"), 'name' => 'GET api/call-log-report/chart-summary'],
            ['module_id' => $this->getModuleBySubmodule("User Activity Report"), 'name' => 'GET api/user-activity-report/list'],
            ['module_id' => $this->getModuleBySubmodule("User Activity Report"), 'name' => 'GET api/user-activity-report/graph'],
            ['module_id' => $this->getModuleBySubmodule("User Activity Report"), 'name' => 'GET api/user-activity-report/user-activity'],
            ['module_id' => $this->getModuleBySubmodule("Pause Code"), 'name' => 'GET api/pause-code'],
            ['module_id' => $this->getModuleBySubmodule("Pause Code"), 'name' => 'GET api/pause-code/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Pause Code"), 'name' => 'POST api/pause-code'],
            ['module_id' => $this->getModuleBySubmodule("Pause Code"), 'name' => 'PUT api/pause-code/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Pause Code"), 'name' => 'DELETE api/pause-code/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Change Password"), 'name' => 'PUT api/update-tenantpassword'],
            ['module_id' => $this->getModuleBySubmodule("Live Agents"), 'name' => 'GET api/live-agents'],
            ['module_id' => $this->getModuleBySubmodule("Live Calls"), 'name' => 'GET api/live-calls'],
            ['module_id' => $this->getModuleBySubmodule("Logout"), 'name' => 'POST api/logout'],
            ['module_id' => $this->getModuleBySubmodule("Product"), 'name' => 'GET api/products'],
            ['module_id' => $this->getModuleBySubmodule("Product"), 'name' => 'GET api/products/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Product"), 'name' => 'POST api/products'],
            ['module_id' => $this->getModuleBySubmodule("Product"), 'name' => 'PUT api/products/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Product"), 'name' => 'DELETE api/products/{id}'],

        ];
        foreach ($subModule as  $subModuleIndex) {
            SubModule::create([
                'module_id' =>$subModuleIndex["module_id"],
                'name' => $subModuleIndex["name"],
            ]);
        }
    }


    public function getModuleBySubmodule($moduleName)
    {
       $moduleInfo =  Module::where("name",$moduleName)->first();
       return $moduleInfo->id ?? 0;
    }
}
