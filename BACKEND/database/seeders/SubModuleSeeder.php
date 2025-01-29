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
        $subModule = [
            ['module_id' => $this->getModuleBySubmodule("Profile"), 'name' => 'GET api/my_profile'],
            ['module_id' =>  $this->getModuleBySubmodule("Support Tickets"), 'name' => 'GET api/supportticket'],
            ['module_id' => $this->getModuleBySubmodule("Support Tickets"), 'name' => 'POST api/supportticket'],
            ['module_id' => $this->getModuleBySubmodule("Support Tickets"), 'name' => 'PUT api/supportticket/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Support Tickets"), 'name' => 'GET api/supportticket/{id}'],
            ['module_id' => $this->getModuleBySubmodule("Support Tickets"), 'name' => 'DELETE api/supportticket/{id}'],
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
            ['module_id' => $this->getModuleBySubmodule("CRON Configuration"), 'name' => 'GET api/cron-commands'],
            ['module_id' => $this->getModuleBySubmodule("CRON Configuration"), 'name' => 'POST api/cron-commands'],
            ['module_id' => $this->getModuleBySubmodule("CRON Configuration"), 'name' => 'PUT api/cron-commands/{id}'],
            ['module_id' => $this->getModuleBySubmodule("CRON Configuration"), 'name' => 'DELETE api/cron-commands/{id}'],
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
            ['module_id' => $this->getModuleBySubmodule("User Groups"), 'name' => 'PUT api/usergroup'],
            ['module_id' => $this->getModuleBySubmodule("User Groups"), 'name' => 'DELETE api/usergroup'],
            
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
