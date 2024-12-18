import { element } from 'prop-types'
import React from 'react'

const Dashboard = React.lazy(() => import('./views/dashboard/Dashboard'))
const Colors = React.lazy(() => import('./views/theme/colors/Colors'))
const Typography = React.lazy(() => import('./views/theme/typography/Typography'))

// Base
const Accordion = React.lazy(() => import('./views/base/accordion/Accordion'))
const Breadcrumbs = React.lazy(() => import('./views/base/breadcrumbs/Breadcrumbs'))
const Cards = React.lazy(() => import('./views/base/cards/Cards'))
const Carousels = React.lazy(() => import('./views/base/carousels/Carousels'))
const Collapses = React.lazy(() => import('./views/base/collapses/Collapses'))
const ListGroups = React.lazy(() => import('./views/base/list-groups/ListGroups'))
const Navs = React.lazy(() => import('./views/base/navs/Navs'))
const Paginations = React.lazy(() => import('./views/base/paginations/Paginations'))
const Placeholders = React.lazy(() => import('./views/base/placeholders/Placeholders'))
const Popovers = React.lazy(() => import('./views/base/popovers/Popovers'))
const Progress = React.lazy(() => import('./views/base/progress/Progress'))
const Spinners = React.lazy(() => import('./views/base/spinners/Spinners'))
const Tables = React.lazy(() => import('./views/base/tables/Tables'))
const Tooltips = React.lazy(() => import('./views/base/tooltips/Tooltips'))

// Buttons
const Buttons = React.lazy(() => import('./views/buttons/buttons/Buttons'))
const ButtonGroups = React.lazy(() => import('./views/buttons/button-groups/ButtonGroups'))
const Dropdowns = React.lazy(() => import('./views/buttons/dropdowns/Dropdowns'))

//Forms
const ChecksRadios = React.lazy(() => import('./views/forms/checks-radios/ChecksRadios'))
const FloatingLabels = React.lazy(() => import('./views/forms/floating-labels/FloatingLabels'))
const FormControl = React.lazy(() => import('./views/forms/form-control/FormControl'))
const InputGroup = React.lazy(() => import('./views/forms/input-group/InputGroup'))
const Layout = React.lazy(() => import('./views/forms/layout/Layout'))
const Range = React.lazy(() => import('./views/forms/range/Range'))
const Select = React.lazy(() => import('./views/forms/select/Select'))
const Validation = React.lazy(() => import('./views/forms/validation/Validation'))

const Charts = React.lazy(() => import('./views/charts/Charts'))

// Icons
const CoreUIIcons = React.lazy(() => import('./views/icons/coreui-icons/CoreUIIcons'))
const Flags = React.lazy(() => import('./views/icons/flags/Flags'))
const Brands = React.lazy(() => import('./views/icons/brands/Brands'))

// Notifications
const Alerts = React.lazy(() => import('./views/notifications/alerts/Alerts'))
const Badges = React.lazy(() => import('./views/notifications/badges/Badges'))
const Modals = React.lazy(() => import('./views/notifications/modals/Modals'))
const Toasts = React.lazy(() => import('./views/notifications/toasts/Toasts'))

const Widgets = React.lazy(() => import('./views/widgets/Widgets'))

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

// Campaign Management
const Ingroups = React.lazy(() => import('./views/inbound/Ingroups'))
const CreateIngroups = React.lazy(() => import('./views/inbound/CreateIngroups'))

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

//Agent
const Agent = React.lazy(() => import('./views/agent/Agent'))

//NeoxDashboard
const NeoxDashboard = React.lazy(() => import('./views/neoxDashboard/NeoxDashboard'))
const LiveCalls = React.lazy(() => import('./views/neoxDashboard/LiveCalls'))
const LiveAgents = React.lazy(() => import('./views/neoxDashboard/LiveAgents'))

// Report Management
const CallCDRreport = React.lazy(() => import('./views/reports/CallCDRreport'))
const AgentActivityReport = React.lazy(() => import('./views/reports/AgentActivityReport'))

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
const EmailConfigSettings = React.lazy(() => import('./views/settings/EmailConfigSettings'))
const CreateEmailConfigSettings = React.lazy(() => import('./views/settings/EmailConfigSettingsForm'))

//Phones
const Phone = React.lazy(() => import('./views/phone/Phones'))
const CreatePhones = React.lazy(() => import('./views/phone/PhonesForm'))

const routes = [
  { path: '/', exact: true, name: 'Home' },
  { path: '/dashboard', name: 'Dashboard', element: Dashboard },
  { path: '/theme', name: 'Theme', element: Colors, exact: true },
  { path: '/theme/colors', name: 'Colors', element: Colors },
  { path: '/theme/typography', name: 'Typography', element: Typography },
  { path: '/base', name: 'Base', element: Cards, exact: true },
  { path: '/base/accordion', name: 'Accordion', element: Accordion },
  { path: '/base/breadcrumbs', name: 'Breadcrumbs', element: Breadcrumbs },
  { path: '/base/cards', name: 'Cards', element: Cards },
  { path: '/base/carousels', name: 'Carousel', element: Carousels },
  { path: '/base/collapses', name: 'Collapse', element: Collapses },
  { path: '/base/list-groups', name: 'List Groups', element: ListGroups },
  { path: '/base/navs', name: 'Navs', element: Navs },
  { path: '/base/paginations', name: 'Paginations', element: Paginations },
  { path: '/base/placeholders', name: 'Placeholders', element: Placeholders },
  { path: '/base/popovers', name: 'Popovers', element: Popovers },
  { path: '/base/progress', name: 'Progress', element: Progress },
  { path: '/base/spinners', name: 'Spinners', element: Spinners },
  { path: '/base/tables', name: 'Tables', element: Tables },
  { path: '/base/tooltips', name: 'Tooltips', element: Tooltips },
  { path: '/buttons', name: 'Buttons', element: Buttons, exact: true },
  { path: '/buttons/buttons', name: 'Buttons', element: Buttons },
  { path: '/buttons/dropdowns', name: 'Dropdowns', element: Dropdowns },
  { path: '/buttons/button-groups', name: 'Button Groups', element: ButtonGroups },
  { path: '/charts', name: 'Charts', element: Charts },
  { path: '/forms', name: 'Forms', element: FormControl, exact: true },
  { path: '/forms/form-control', name: 'Form Control', element: FormControl },
  { path: '/forms/select', name: 'Select', element: Select },
  { path: '/forms/checks-radios', name: 'Checks & Radios', element: ChecksRadios },
  { path: '/forms/range', name: 'Range', element: Range },
  { path: '/forms/input-group', name: 'Input Group', element: InputGroup },
  { path: '/forms/floating-labels', name: 'Floating Labels', element: FloatingLabels },
  { path: '/forms/layout', name: 'Layout', element: Layout },
  { path: '/forms/validation', name: 'Validation', element: Validation },
  { path: '/icons', exact: true, name: 'Icons', element: CoreUIIcons },
  { path: '/icons/coreui-icons', name: 'CoreUI Icons', element: CoreUIIcons },
  { path: '/icons/flags', name: 'Flags', element: Flags },
  { path: '/icons/brands', name: 'Brands', element: Brands },
  { path: '/notifications', name: 'Notifications', element: Alerts, exact: true },
  { path: '/notifications/alerts', name: 'Alerts', element: Alerts },
  { path: '/notifications/badges', name: 'Badges', element: Badges },
  { path: '/notifications/modals', name: 'Modals', element: Modals },
  { path: '/notifications/toasts', name: 'Toasts', element: Toasts },
  { path: '/widgets', name: 'Widgets', element: Widgets },

  { path: '/my_profile', name: 'Profile', element: Profile },
  { path: '/test_report', name: 'Test Report', element: TestReport },
  { path: '/user', name: 'User Management', element: Users, exact: true },
  { path: '/user/users', name: 'Users', element: Users },
  { path: '/user/crt_usr', name: 'Create Users', element: CreateUser },
  { path: '/user/edit/:id', name: 'Edit Tenant', element: CreateUser },
  { path: '/user/user_groups', name: 'Users Groups', element: UserGroups },
  { path: '/user/crt_usr_grp', name: 'Create User Groups', element: CreateUserGroups },
  { path: '/user/edit_usergroup/:id', name: 'Edit UserGroup', element: CreateUserGroups },

  { path: '/campaign', name: 'Campaign Management', element: Campaigns, exact: true },
  { path: '/campaign/campaigns', name: 'Campaigns', element: Campaigns },
  { path: '/campaign/create_campaign', name: 'Create Campaign', element: CreateCampaign },

  { path: '/inbound', name: 'Inbound Management', element: Ingroups, exact: true },
  { path: '/inbound/ingrps', name: 'In-Groups', element: Ingroups },
  { path: '/inbound/create_ingrps', name: 'Create In-Groups', element: CreateIngroups },

  { path: '/tenants', name: 'Tenant Management', element: Tenants },
  { path: '/tenants/tenants', name: 'Tenants', element: Tenants },
  { path: '/tenants/crt_tenant', name: 'Create Tenant', element: CreateTenants },
  { path: '/tenants/edit/:id', name: 'Edit Tenant', element: CreateTenants },

  { path: '/tenants/tenant_role/', name: 'Create Tenant', element: TenantRoles },
  {
    path: '/tenants/tenant_role/crt_tenant_role',
    name: 'Create Tenant Role',
    element: CreateTenantRoles,
  },
  { path: '/tenants/tenant_role/edit/:id', name: 'Edit Tenant Role', element: CreateTenantRoles },

  { path: '/password_reset', name: 'Tenant Password Reset', element: TenantPasswordReset },

  { path: '/report/callCDR', name: 'Report Management', element: CallCDRreport },
  { path: '/report/agent-activity', name: 'Report Management', element: AgentActivityReport },

  { path: '/accounts', name: 'Accounts', element: Accounts, exact: true },
  { path: '/accounts/create_accounts', name: 'Create Accounts', element: CreateAccounts },
  { path: '/accounts/edit/:id', name: 'Edit Accounts', element: CreateAccounts },

  { path: '/contacts', name: 'Contacts', element: Contacts, exact: true },
  { path: '/contacts/create_contacts', name: 'Create Contacts', element: CreateContacts },
  { path: '/contacts/edit/:id', name: 'Edit Contacts', element: CreateContacts },

  { path: '/spprt_tckt', name: 'Support Ticket', element: SupportTickets, exact: true },
  { path: '/spprt_tckt/crt_tckt', name: 'Create Support Ticket', element: CreateSupportTickets },
  { path: '/spprt_tckt/edit/:id', name: 'Edit Support Ticket', element: CreateSupportTickets },

  { path: '/spprt_tckt_view/:id', name: 'Support Ticket', element: ViewSupportTickets },

  { path: '/email_config_settings', name: 'Email Configuration Settings', element: EmailConfigSettings },
  { path: '/email_config_settings/crt_config', name: 'Create Email Configuration Settings', element: CreateEmailConfigSettings },
  { path: '/email_config_settings/edit/:id', name: 'Edit Email Configuration Settings', element: CreateEmailConfigSettings },

  { path: '/agent', name: 'Agent', element: Agent, exact: true },

  { path: '/agent/spprt_tckt', name: 'Support Ticket', element: SupportTickets, exact: true },
  { path: '/agent/spprt_tckt/crt_tckt', name: 'Create Support Ticket', element: CreateSupportTickets },
  { path: '/agent/spprt_tckt/edit/:id', name: 'Edit Support Ticket', element: CreateSupportTickets },
  { path: '/agent/spprt_tckt_view/:id', name: 'Support Ticket', element: ViewSupportTickets },

  { path: '/neox_dash', name: 'Dashboard', element: NeoxDashboard, exact: true },
  { path: '/neox_dash/live_calls', name: 'Live Calls', element: LiveCalls },
  { path: '/neox_dash/live_agents', name: 'Live Agents', element: LiveAgents },

  { path: '/license', name: 'License', element: License, exact: true },
  { path: '/tenant_license/:id', name: 'Edit Tenant', element: CreateTenantsLicense },

  { path: '/phones', name: 'Phones', element: Phone, exact: true },
  { path: '/phones/create_phone', name: 'Create Phone', element: CreatePhones },
  { path: '/phones/edit/:id', name: 'Edit Phone', element: CreatePhones },
]

export default routes
