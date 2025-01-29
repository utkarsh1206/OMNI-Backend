import React from 'react'
import CIcon from '@coreui/icons-react'
import * as icon from '@coreui/icons'
import { CNavGroup, CNavGroupItems, CNavItem, CNavTitle } from '@coreui/react'
import { jwtDecode } from 'jwt-decode'

const navItemList = [
  // {
  //   component: CNavItem,
  //   name: 'Test Report',
  //   to: '/test_report',
  //   // icon: <CIcon icon={icon.cilChart} customClassName="nav-icon" style={{ color: 'white' }} />,
  //   icon: <CIcon icon={icon.cilChart} customClassName="nav-icon" />,
  //   badge: {
  //     color: 'success',
  //     text: 'IMP',
  //   },
  // },
  {
    component: CNavTitle,
    name: 'Components',
  },
  {
    component: CNavGroup,
    name: 'Tenant Management',
    to: '/tenants',
    icon: <CIcon icon={icon.cilBuilding} customClassName="nav-icon" />,
    items: [
      {
        component: CNavItem,
        name: 'Tenants',
        to: '/tenants/tenants',
      },
      {
        component: CNavItem,
        name: 'Roles',
        to: '/tenants/tenant_role',
      },
    ],
  },
  {
    component: CNavGroup,
    name: 'User Management',
    to: '/user',
    icon: <CIcon icon={icon.cilUser} customClassName="nav-icon" />,
    items: [
      {
        component: CNavItem,
        name: 'Users',
        to: '/user/users',
      },
      {
        component: CNavItem,
        name: 'User Groups',
        to: '/user/user_groups',
      },
    ],
  },
  {
    component: CNavItem,
    name: 'Support Tickets',
    to: '/spprt_tckt',
    icon: <CIcon icon={icon.cilTags} customClassName="nav-icon" />,
  },
  {
    component: CNavItem,
    name: 'Accounts',
    to: '/accounts',
    icon: <CIcon icon={icon.cilNewspaper} customClassName="nav-icon" />,
  },
  {
    component: CNavItem,
    name: 'Contacts',
    to: '/contacts',
    icon: <CIcon icon={icon.cilContact} customClassName="nav-icon" />,
  },
  {
    component: CNavItem,
    name: 'Departments',
    to: '/department',
    icon: <CIcon icon={icon.cilFolderOpen} customClassName="nav-icon" />,
  },
  {
    component: CNavItem,
    name: 'Categories',
    to: '/category',
    icon: <CIcon icon={icon.cilGrid} customClassName="nav-icon" />,
  },
  {
    component: CNavItem,
    name: 'Incident Type',
    to: '/incdnt_type',
    icon: <CIcon icon={icon.cilWarning} customClassName="nav-icon" />,
  },
  {
    component: CNavGroup,
    name: 'Campaign Mgmt',
    to: '/campaign',
    icon: <CIcon icon={icon.cilCursor} customClassName="nav-icon" />,
    items: [
      {
        component: CNavItem,
        name: 'Campaigns',
        to: '/campaign/campaigns',
      },
      {
        component: CNavItem,
        name: 'Pause Codes',
        to: '/campaign/pause_codes',
      },
      {
        component: CNavItem,
        name: 'List Mix',
        to: '/campaign/list_mix',
      },
      {
        component: CNavItem,
        name: 'Dynamic Prompt',
        to: '/campaign/dyn_prompt',
      },
      {
        component: CNavItem,
        name: 'Conference',
        to: '/campaign/conference',
      },
      {
        component: CNavItem,
        name: 'SMS API Gateway',
        to: '/campaign/sms_api_gateway',
      },
    ],
  },
  {
    component: CNavGroup,
    name: 'List Mgmt',
    to: '/list',
    icon: <CIcon icon={icon.cilNotes} customClassName="nav-icon" />,
    items: [
      {
        component: CNavItem,
        name: 'List',
        to: '/list/list',
      },
      {
        component: CNavItem,
        name: 'Load New Leads',
        to: '/list/load_n_leads',
      },
      {
        component: CNavItem,
        name: 'List Custom Fields',
        to: '/list/lst_cstm_flds',
      },
      {
        component: CNavItem,
        name: 'Copy Custom Fields',
        to: '/list/cpy_cstm_flds',
      },
      {
        component: CNavItem,
        name: 'Lead Rechurn',
        to: '/list/lead_rchrn',
      },
      {
        component: CNavItem,
        name: 'Callback Mgmt',
        to: '/list/clbck_mgmt',
      },
      {
        component: CNavItem,
        name: 'CRM Database Config',
        to: '/list/crm_db_conf',
      },
    ],
  },
  {
    component: CNavGroup,
    name: 'Inbound Mgmt',
    to: '/inbound',
    icon: <CIcon icon={icon.cilArrowThickFromTop} customClassName="nav-icon" />,
    items: [
      {
        component: CNavItem,
        name: 'In-groups',
        to: '/inbound/ingrps',
      },
      {
        component: CNavItem,
        name: 'DIDs',
        to: '/inbound/dids',
      },
      {
        component: CNavItem,
        name: 'Call Menus',
        to: '/inbound/call_menu',
      },
    ],
  },
  {
    component: CNavGroup,
    name: 'Service & Feature',
    to: '/svc_n_ft',
    icon: <CIcon icon={icon.cilStar} customClassName="nav-icon" />,
    items: [
      {
        component: CNavItem,
        name: 'Scripts',
        to: '/svc_n_ft/scripts',
      },
      {
        component: CNavGroup,
        name: 'DNC Number',
        to: '/svc_n_ft/dnc',
        items: [
          {
            component: CNavItem,
            name: 'Global DNC',
            to: '/svc_n_ft/dnc/global',
          },
          {
            component: CNavItem,
            name: 'Local DNC',
            to: '/svc_n_ft/dnc/local',
          },
        ],
      },
      {
        component: CNavItem,
        name: 'Number Profile',
        to: '/svc_n_ft/num_prof',
      },
      {
        component: CNavItem,
        name: 'Disposition Statuses',
        to: '/svc_n_ft/disp_status',
      },
      {
        component: CNavItem,
        name: 'Auth Token API',
        to: '/svc_n_ft/auth_tkn_api',
      },
    ],
  },
  {
    component: CNavGroup,
    name: 'Call Time Mgmt',
    to: '/call_time_mgmt',
    icon: <CIcon icon={icon.cilClock} customClassName="nav-icon" />,
    items: [
      {
        component: CNavItem,
        name: 'Call Times',
        to: '/call_time_mgmt/call_times',
      },
      {
        component: CNavItem,
        name: 'State Call Times',
        to: '/call_time_mgmt/state_call_times',
      },
      {
        component: CNavItem,
        name: 'Shifts',
        to: '/call_time_mgmt/shifts',
      },
    ],
  },
  {
    component: CNavGroup,
    name: 'ACL Mgmt',
    to: '/acl_mgmt',
    icon: <CIcon icon={icon.cilFingerprint} customClassName="nav-icon" />,
    items: [
      {
        component: CNavItem,
        name: 'ACL User',
        to: '/acl_mgmt/acl_user',
      },
      {
        component: CNavItem,
        name: 'ACL Profile',
        to: '/acl_mgmt/acl_prof',
      },
      {
        component: CNavItem,
        name: 'ACL User Profile Binding',
        to: '/acl_mgmt/acl_usr_prof_bind',
      },
    ],
  },
  {
    component: CNavGroup,
    name: 'Report Mgmt',
    to: '/report',
    icon: <CIcon icon={icon.cilBarcode} customClassName="nav-icon" />,
    items: [
      {
        component: CNavItem,
        name: 'Call CDR Report',
        to: '/report/callCDR',
      },
      {
        component: CNavItem,
        name: 'Agent Activity Report',
        to: '/report/agent-activity',
      },
      {
        component: CNavItem,
        name: 'Ticket Report',
        to: '/report/ticket-report',
      },
      {
        component: CNavItem,
        name: 'SLA Report',
        to: '/report/sla-report',
      },
      {
        component: CNavItem,
        name: 'Agent Report',
        to: '/report/agent',
      },
      {
        component: CNavItem,
        name: 'Inbound Report',
        to: '/report/inbound',
      },
      {
        component: CNavItem,
        name: 'Outbound Report',
        to: '/report/outbound',
      },
    ],
  },
  {
    component: CNavGroup,
    name: 'Bulk Operations',
    to: '/bulk',
    icon: <CIcon icon={icon.cilBasket} customClassName="nav-icon" />,
    items: [
      {
        component: CNavItem,
        name: 'User-Usergroup Mgmt',
        to: '/bulk/usr_usrgrp',
      },
      {
        component: CNavItem,
        name: 'User-Ingroup Mgmt',
        to: '/bulk/usr_ingrp',
      },
      {
        component: CNavItem,
        name: 'User-Campaign Mgmt',
        to: '/bulk/usr_camp',
      },
      {
        component: CNavItem,
        name: 'Bulk Phone Creation',
        to: '/bulk/phn_crtn',
      },
      {
        component: CNavItem,
        name: 'Bulk User Creation',
        to: '/bulk/usr_crtn',
      },
    ],
  },
  {
    component: CNavItem,
    name: 'Phones',
    to: '/phones',
    icon: <CIcon icon={icon.cilPhone} customClassName="nav-icon" />,
  },
  {
    component: CNavGroup,
    name: 'System Management',
    to: '/sys',
    icon: <CIcon icon={icon.cilBell} customClassName="nav-icon" />,
    items: [
      {
        component: CNavItem,
        name: 'Server Mgmt',
        to: '/sys/srvr',
      },
      {
        component: CNavItem,
        name: 'Third Party Auth',
        to: '/sys/thrd_prty_auth',
      },
      {
        component: CNavItem,
        name: 'Customize GUI',
        to: '/sys/cstmz_gui',
      },
    ],
  },
  {
    component: CNavGroup,
    name: 'Pages',
    icon: <CIcon icon={icon.cilStar} customClassName="nav-icon" />,
    items: [
      {
        component: CNavItem,
        name: 'Login',
        to: '/login',
      },
      {
        component: CNavItem,
        name: 'Register',
        to: '/register',
      },
      {
        component: CNavItem,
        name: 'Error 404',
        to: '/404',
      },
      {
        component: CNavItem,
        name: 'Error 500',
        to: '/500',
      },
    ],
  },
  {
    component: CNavItem,
    name: 'Agent',
    to: '/agent',
    icon: <CIcon icon={icon.cilUserPlus} customClassName="nav-icon" />,
  },
  // {
  //   component: CNavItem,
  //   name: 'Support Tickets view',
  //   to: '/spprt_tckt_view',
  //   icon: <CIcon icon={icon.cilUserPlus} customClassName="nav-icon" />,
  // },

  // {
  //   component: CNavItem,
  //   name: 'Settings1',
  //   to: '/email_config_settings',
  //   icon: <CIcon icon={icon.cilSettings} customClassName="nav-icon" />,
  // },
  {
    component: CNavItem,
    name: 'Settings',
    to: '/settings',
    icon: <CIcon icon={icon.cilSettings} customClassName="nav-icon" />,
  },
]

const NAV_PERMISSIONS = {
  '/tenants/tenants': 'viewTenants',
  '/tenants/tenant_role': 'viewRoles',
  '/user/users': 'viewUsers',
  '/user/user_groups': 'viewUserGroups',
  // '/spprt_tckt': 'viewSupportTicket',
  '/campaign/campaigns': 'viewCampaigns',
  '/campaign/pause_codes': 'viewPauseCodes',
  '/campaign/list_mix': 'viewListMix',
  '/campaign/dyn_prompt': 'viewDynamicPrompt',
  '/campaign/conference': 'viewConference',
  '/campaign/sms_api_gateway': 'viewSMSAPIGateway',
  '/list/list': 'viewList',
  '/list/load_n_leads': 'viewLoadNewList',
  '/list/lst_cstm_flds': 'viewCustomFields',
  '/list/cpy_cstm_flds': 'ViewCopyCustomFields',
  '/list/lead_rchrn': 'viewLeadRechurn',
  '/list/clbck_mgmt': 'viewCallbackManagement',
  '/list/crm_db_conf': 'viewCRMDBConfig',
  '/inbound/ingrps': 'viewIngroups',
  '/inbound/dids': 'viewDIDs',
  '/inbound/call_menu': 'viewCallMenu',
  '/svc_n_ft/scripts': 'viewScripts',
  '/svc_n_ft/dnc': 'viewDNC',
  '/svc_n_ft/dnc/global': 'viewGlobalDNC',
  '/svc_n_ft/dnc/local': 'viewLocalDNC',
  '/svc_n_ft/num_prof': 'viewNumProf',
  '/svc_n_ft/disp_status': 'viewDispoStatus',
  '/svc_n_ft/auth_tkn_api': 'viewAuthTokenAPI',
  '/call_time_mgmt/call_times': 'viewCallTimes',
  '/call_time_mgmt/state_call_times': 'viewStateCallTimes',
  '/call_time_mgmt/shifts': 'viewShifts',
  '/acl_mgmt/acl_user': 'viewACLUsers',
  '/acl_mgmt/acl_prof': 'viewACLProfile',
  '/acl_mgmt/acl_usr_prof_bind': 'viewACLUserProfBinding',
  '/report/agent': 'viewAgentReport',
  '/report/inbound': 'viewInboundReport',
  '/report/outbound': 'viewOutboundReport',
  '/bulk/usr_usrgrp': 'viewBulkUserGroup',
  '/bulk/usr_ingrp': 'viewBulkUserIngroup',
  '/bulk/usr_camp': 'viewBulkUserCampaign',
  '/bulk/phn_crtn': 'viewBulkPhoneCreation',
  '/bulk/usr_crtn': 'viewBulkUserCreation',
  // '/phones': 'viewPhones',
  '/sys/srvr': 'viewServerManagement',
  '/sys/thrd_prty_auth': 'viewThirdPartyAuth',
  '/sys/cstmz_gui': 'viewCustomizeGUI',
  '/agent': 'viewAgent',
  '/accounts/accounts': 'viewAccounts',
  '/contacts/contacts': 'viewContacts',
}

const filterNavItems = (navItems, userPermissions) => {
  if (userPermissions != '') {
    return navItems
      .filter((item) => {
        if (item.items) {
          item.items = filterNavItems(item.items, userPermissions)
        }
        const requiredPermission = NAV_PERMISSIONS[item.to]
        return !requiredPermission || userPermissions.includes(requiredPermission)
      })
      .filter((item) => !item.items || item.items.length > 0)
  }
  return navItems
}

const getUserPermissions = () => {
  const token = localStorage.getItem('token')
  const decoded = jwtDecode(token)
  return decoded.permission || []
}

// Get user permissions and filtered navigation items
const userPermissions = getUserPermissions()
const _nav = filterNavItems(navItemList, userPermissions)

export default _nav
