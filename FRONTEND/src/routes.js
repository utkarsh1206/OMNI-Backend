import React from 'react'

const Dashboard = React.lazy(() => import('./views/dashboard/Dashboard'))
const TestReport = React.lazy(() => import('./views/reports/TestReport'))
const Profile = React.lazy(() => import('./views/profile/Profile'))

// Password Reset
const TenantPasswordReset = React.lazy(() => import('./views/tenant/ForgotPassword'))

// User Management
const Users = React.lazy(() => import('./views/user/Users'))
const CreateUser = React.lazy(() => import('./views/user/UserForm'))
const UserGroups = React.lazy(() => import('./views/user/UserGroups'))
const CreateUserGroups = React.lazy(() => import('./views/user/UserGroupForm'))

// Campaign Management
const Campaigns = React.lazy(() => import('./views/campaign/Campaigns'))
const CreateCampaign = React.lazy(() => import('./views/campaign/CreateCampaign'))

const Ingroups = React.lazy(() => import('./views/inbound/Ingroups'))
const CreateIngroups = React.lazy(() => import('./views/inbound/CreateIngroups'))

const Departments = React.lazy(() => import('./views/department/Departments'))
const CreateDepartments = React.lazy(() => import('./views/department/DepartmentsForm'))

const Categories = React.lazy(() => import('./views/category/Categories'))
const CreateCategories = React.lazy(() => import('./views/category/CategoriesForm'))

const Incident = React.lazy(() => import('./views/incident/Incident'))
const CreateIncident = React.lazy(() => import('./views/incident/IncidentForm'))

const ProductPage = React.lazy(() => import('./views/product/ProductPage'));
const ProductForm = React.lazy(() => import('./views/product/ProductForm'));

const Chatbot = React.lazy(() => import('./views/chatbot/Chatbot'));
const ChatbotForm = React.lazy(() => import('./views/chatbot/ChatbotForm'));

// Tenant Management
const Tenants = React.lazy(() => import('./views/tenant/Tenants'))
const CreateTenants = React.lazy(() => import('./views/tenant/TenantsForm'))

// Tenant Role Management
const TenantRoles = React.lazy(() => import('./views/roles/TenantRole'))
const CreateTenantRoles = React.lazy(() => import('./views/roles/TenantRoleForm'))
// const EditTenantRoles = React.lazy(() => import('./views/roles/TenantRoleForm'))

const SupportTickets = React.lazy(() => import('./views/supportTicket/SupportTickets'))
const CreateSupportTickets = React.lazy(() => import('./views/supportTicket/SupportTicketsForm'))
const ViewSupportTickets = React.lazy(() => import('./views/supportTicket/ViewSupportTickets'))

const SmsProvider = React.lazy(() => import('./views/smsProvider/SmsProvider'))
const CreateSmsProvider = React.lazy(() => import('./views/smsProvider/SmsProviderForm'))

//Agent
const Agent = React.lazy(() => import('./views/agent/Agent'))

//NeoxDashboard
const NeoxDashboard = React.lazy(() => import('./views/neoxDashboard/NeoxDashboard'))
const LiveCalls = React.lazy(() => import('./views/neoxDashboard/LiveCalls'))
const LiveAgents = React.lazy(() => import('./views/neoxDashboard/LiveAgents'))

// Report Management
const CallCDRreport = React.lazy(() => import('./views/reports/CallCDRreport'))
const AgentActivityReport = React.lazy(() => import('./views/reports/AgentActivityReport'))
const TicketReport = React.lazy(() => import('./views/reports/TicketReport'))
const SLAReport = React.lazy(() => import('./views/reports/SLAReport'))
const AuditReport = React.lazy(() => import('./views/reports/AuditReport'))
const EmailLogs = React.lazy(() => import('./views/reports/EmailLogs'))


//Accounts
const Accounts = React.lazy(() => import('./views/accounts/Accounts'))
const CreateAccounts = React.lazy(() => import('./views/accounts/AccountForm'))

//Contacts
const Contacts = React.lazy(() => import('./views/contacts/Contacts'))
const CreateContacts = React.lazy(() => import('./views/contacts/ContactForm'))

//License
const License = React.lazy(() => import('./views/license/License'))
const CreateTenantsLicense = React.lazy(() => import('./views/license/TenantsLicenseForm'))

// Settings
const SettingsOptions = React.lazy(() => import('./views/settings/SettingsOptions'))
const EmailSettingsOptions = React.lazy(() => import('./views/settings/email/EmailSettingsOptions'))
const EmailConfigSettings = React.lazy(() => import('./views/settings/email/EmailConfigSettings'))
const CreateEmailConfigSettings = React.lazy(() => import('./views/settings/email/EmailConfigSettingsForm'))

const EmailTemplates = React.lazy(() => import('./views/settings/emailTemplate/EmailTemplates'))
const CreateEmailTemplate = React.lazy(() => import('./views/settings/emailTemplate/EmailTemplatesForm'))

const SLAConfigList = React.lazy(() => import('./views/settings/sla/SLAConfiguration'))
const SLAConfiguration = React.lazy(() => import('./views/settings/sla/SLAConfigurationForm'))

const CRONConfigList = React.lazy(() => import('./views/settings/cron/cronConfigs'))
const CRONConfiguration = React.lazy(() => import('./views/settings/cron/cronConfigsForm'))

//Phones
const Phone = React.lazy(() => import('./views/phone/Phones'))
const CreatePhones = React.lazy(() => import('./views/phone/PhonesForm'))

const routes = [
  { path: '/', exact: true, name: 'Home', permissionTag: true },
  { path: '/dashboard', name: 'Dashboard', element: Dashboard, permissionTag: true },
  { path: '/my_profile', name: 'Profile', element: Profile, permissionTag: true },
  { path: '/test_report', name: 'Test Report', element: TestReport, permissionTag: true },
  { path: '/user', name: 'User Management', element: Users, exact: true, permissionTag: true },
  { path: '/user/users', name: 'Users', element: Users, permissionTag: true },
  { path: '/user/crt_usr', name: 'Create Users', element: CreateUser, permissionTag: true },
  { path: '/user/edit/:id', name: 'Edit Tenant', element: CreateUser, permissionTag: true },
  { path: '/user/user_groups', name: 'Users Groups', element: UserGroups, permissionTag: true },
  { path: '/user/crt_usr_grp', name: 'Create User Groups', element: CreateUserGroups, permissionTag: true },
  { path: '/user/edit_usergroup/:id', name: 'Edit UserGroup', element: CreateUserGroups, permissionTag: true },

  { path: '/campaign', name: 'Campaign Management', element: Campaigns, exact: true, permissionTag: true },
  { path: '/campaign/campaigns', name: 'Campaigns', element: Campaigns, permissionTag: true },
  { path: '/campaign/create_campaign', name: 'Create Campaign', element: CreateCampaign, permissionTag: true },

  { path: '/inbound', name: 'Inbound Management', element: Ingroups, exact: true, permissionTag: true },
  { path: '/inbound/ingrps', name: 'In-Groups', element: Ingroups, permissionTag: true },
  { path: '/inbound/create_ingrps', name: 'Create In-Groups', element: CreateIngroups, permissionTag: true },

  { path: '/department', name: 'Department Management', element: Departments, exact: true, permissionTag: true },
  { path: '/department/departments', name: 'Department', element: Departments, permissionTag: true },
  { path: '/department/create_departments', name: 'Create Department', element: CreateDepartments, permissionTag: true },
  { path: '/department/edit_departments/:id', name: 'Edit Department', element: CreateDepartments, permissionTag: true },

  { path: '/category', name: 'Category Management', element: Categories, exact: true, permissionTag: true },
  { path: '/category/categories', name: 'Category', element: Categories, permissionTag: true },
  { path: '/category/create_categories', name: 'Create Category', element: CreateCategories, permissionTag: true },
  { path: '/category/edit_categories/:id', name: 'Edit Category', element: CreateCategories, permissionTag: true },

  { path: '/incdnt_type', name: 'Incident Type', element: Incident, exact: true, permissionTag: true },
  // { path: '/incdnt_type/categories', name: 'Category', element: Categories, permissionTag: true},
  { path: '/incdnt_type/create_incident', name: 'Create Incident', element: CreateIncident, permissionTag: true },
  { path: '/incdnt_type/edit_incident/:id', name: 'Edit Incident', element: CreateIncident, permissionTag: true },

  { path: '/sms_provider', name: 'SMS Provider Management', element: SmsProvider, permissionTag: true },
  { path: '/sms_provider/sms_provider', name: 'SMS Provider', element: SmsProvider, permissionTag: true },
  { path: '/sms_provider/crt_sms_provider', name: 'Create SMS Provider', element: CreateSmsProvider, permissionTag: true },
  { path: '/sms_provider/edit/:id', name: 'Edit SMS Provider', element: CreateSmsProvider, permissionTag: true },

  { path: '/product', name: 'Product List', element: ProductPage, permissionTag: true },
  { path: '/product/create', name: 'Create Product', element: ProductForm, permissionTag: true },
  { path: '/product/edit/:id', name: 'Edit Product', element: ProductForm, permissionTag: true },

  { path: '/chatbot', name: 'Chatbot List', element: Chatbot, permissionTag: true },
  { path: '/chatbot/create', name: 'Create Chatbot', element: ChatbotForm, permissionTag: true },
  { path: '/chatbot/edit/:id', name: 'Edit Chatbot', element: ChatbotForm, permissionTag: true },

  { path: '/tenants', name: 'Tenant Management', element: Tenants, permissionTag: true },
  { path: '/tenants/tenants', name: 'Tenants', element: Tenants, permissionTag: true },
  { path: '/tenants/crt_tenant', name: 'Create Tenant', element: CreateTenants, permissionTag: true },
  { path: '/tenants/edit/:id', name: 'Edit Tenant', element: CreateTenants, permissionTag: true },

  { path: '/tenants/tenant_role/', name: 'Create Tenant', element: TenantRoles, permissionTag: true },
  { path: '/tenants/tenant_role/crt_tenant_role', name: 'Create Tenant Role', element: CreateTenantRoles, permissionTag: true },
  { path: '/tenants/tenant_role/edit/:id', name: 'Edit Tenant Role', element: CreateTenantRoles, permissionTag: true },

  { path: '/password_reset', name: 'Tenant Password Reset', element: TenantPasswordReset, permissionTag: true },

  { path: '/report/callCDR', name: 'Report Management', element: CallCDRreport, permissionTag: true },
  { path: '/report/agent-activity', name: 'Report Management', element: AgentActivityReport, permissionTag: true },
  { path: '/report/ticket-report', name: 'Report Management', element: TicketReport, permissionTag: true },
  { path: '/report/sla-report', name: 'Report Management', element: SLAReport, permissionTag: true },
  { path: '/report/audit-report', name: 'Audit Report', element: AuditReport, permissionTag: true },
  { path: '/report/EmailLogs', name: 'EmailLogs', element: EmailLogs, exact: true, permissionTag: true },

  { path: '/accounts', name: 'Accounts', element: Accounts, exact: true, permissionTag: true },
  { path: '/accounts/create_accounts', name: 'Create Accounts', element: CreateAccounts, permissionTag: true },
  { path: '/accounts/edit/:id', name: 'Edit Accounts', element: CreateAccounts, permissionTag: true },

  { path: '/contacts', name: 'Contacts', element: Contacts, exact: true, permissionTag: true },
  { path: '/contacts/create_contacts', name: 'Create Contacts', element: CreateContacts, permissionTag: true },
  { path: '/contacts/edit/:id', name: 'Edit Contacts', element: CreateContacts, permissionTag: true },

  { path: '/spprt_tckt', name: 'Support Ticket', element: SupportTickets, exact: true, permissionTag: true },
  { path: '/spprt_tckt/crt_tckt', name: 'Create Support Ticket', element: CreateSupportTickets, permissionTag: true },
  { path: '/spprt_tckt/edit/:id', name: 'Edit Support Ticket', element: CreateSupportTickets, permissionTag: true },

  { path: '/spprt_tckt_view/:id', name: 'Support Ticket', element: ViewSupportTickets, permissionTag: true },

  { path: '/settings', name: 'Settings', element: SettingsOptions, permissionTag: true },

  { path: '/settings/email_options', name: 'Email Options', element: EmailSettingsOptions, permissionTag: true },
  { path: '/settings/email_config_settings', name: 'Email Configuration Settings', element: EmailConfigSettings, permissionTag: true },
  { path: '/settings/email_config_settings/crt_config', name: 'Create Email Configuration Settings', element: CreateEmailConfigSettings, permissionTag: true },
  { path: '/settings/email_config_settings/edit/:id', name: 'Edit Email Configuration Settings', element: CreateEmailConfigSettings, permissionTag: true },

  { path: '/settings/email_templates', name: 'Email Templates', element: EmailTemplates, permissionTag: true },
  { path: '/settings/email_templates/crt_tmplt', name: 'Create Email Template', element: CreateEmailTemplate, permissionTag: true },
  { path: '/settings/email_templates/edit/:id', name: 'Edit Email Template', element: CreateEmailTemplate, permissionTag: true },

  { path: '/settings/SLA_config_list', name: 'SLA Configuration', element: SLAConfigList, permissionTag: true },
  { path: '/settings/SLA_configuration', name: 'Create SLA Configuration', element: SLAConfiguration, permissionTag: true },
  { path: '/settings/SLA_configuration/edit/:id', name: 'Edit SLA Configuration', element: SLAConfiguration, permissionTag: true },

  { path: '/settings/CRON_configs', name: 'CRON Configuration', element: CRONConfigList, permissionTag: true },
  { path: '/settings/CRON_configs/crt_cron', name: 'Create CRON Configuration', element: CRONConfiguration, permissionTag: true },
  { path: '/settings/CRON_configs/edit/:id', name: 'Edit CRON Configuration', element: CRONConfiguration, permissionTag: true },

  { path: '/agent', name: 'Agent', element: Agent, exact: true, permissionTag: true },
  { path: '/agent/dashboard', name: 'Agent Dashboard', element: Dashboard, exact: true, permissionTag: true },

  { path: '/agent/spprt_tckt', name: 'Support Ticket', element: SupportTickets, exact: true, permissionTag: true },
  { path: '/agent/spprt_tckt/crt_tckt', name: 'Create Support Ticket', element: CreateSupportTickets, permissionTag: true },
  { path: '/agent/spprt_tckt/edit/:id', name: 'Edit Support Ticket', element: CreateSupportTickets, permissionTag: true },
  { path: '/agent/spprt_tckt_view/:id', name: 'Support Ticket', element: ViewSupportTickets, permissionTag: true },

  { path: '/neox_dash', name: 'Dashboard', element: NeoxDashboard, exact: true, permissionTag: true },
  { path: '/neox_dash/live_calls', name: 'Live Calls', element: LiveCalls, permissionTag: true },
  { path: '/neox_dash/live_agents', name: 'Live Agents', element: LiveAgents, permissionTag: true },

  { path: '/license', name: 'License', element: License, exact: true, permissionTag: true },
  { path: '/tenant_license/:id', name: 'Edit Tenant', element: CreateTenantsLicense, permissionTag: true },

  { path: '/phones', name: 'Phones', element: Phone, exact: true, permissionTag: true },
  { path: '/phones/create_phone', name: 'Create Phone', element: CreatePhones, permissionTag: true },
  { path: '/phones/edit/:id', name: 'Edit Phone', element: CreatePhones, permissionTag: true },
]

export default routes
