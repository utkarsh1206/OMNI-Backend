import React from 'react'
import CIcon from '@coreui/icons-react'
import {
  cilArrowThickFromTop,
  cilBarcode,
  cilBasket,
  cilBell,
  cilChart,
  cilClock,
  cilCursor,
  cilFingerprint,
  cilNotes,
  cilPhone,
  cilSpeedometer,
  cilStar,
  cilUser,
  cilUserPlus,
} from '@coreui/icons'
import { CNavGroup, CNavGroupItems, CNavItem, CNavTitle } from '@coreui/react'

const _nav = [
  {
    component: CNavItem,
    name: 'Test Report',
    to: '/test_report',
    icon: <CIcon icon={cilChart} customClassName="nav-icon" />,
    badge: {
      color: 'success',
      text: 'IMP',
    },
  },
  {
    component: CNavTitle,
    name: 'Components',
  },
  {
    component: CNavGroup,
    name: 'User Management',
    to: '/user',
    icon: <CIcon icon={cilUser} customClassName="nav-icon" />,
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
    component: CNavGroup,
    name: 'Campaign Mgmt',
    to: '/campaign',
    icon: <CIcon icon={cilCursor} customClassName="nav-icon" />,
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
    icon: <CIcon icon={cilNotes} customClassName="nav-icon" />,
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
    icon: <CIcon icon={cilArrowThickFromTop} customClassName="nav-icon" />,
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
    icon: <CIcon icon={cilStar} customClassName="nav-icon" />,
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
    icon: <CIcon icon={cilClock} customClassName="nav-icon" />,
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
    icon: <CIcon icon={cilFingerprint} customClassName="nav-icon" />,
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
    icon: <CIcon icon={cilBarcode} customClassName="nav-icon" />,
    items: [
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
    icon: <CIcon icon={cilBasket} customClassName="nav-icon" />,
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
    icon: <CIcon icon={cilPhone} customClassName="nav-icon" />,
  },
  {
    component: CNavGroup,
    name: 'System Management',
    to: '/sys',
    icon: <CIcon icon={cilBell} customClassName="nav-icon" />,
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
    icon: <CIcon icon={cilStar} customClassName="nav-icon" />,
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
    icon: <CIcon icon={cilUserPlus} customClassName="nav-icon" />,
  },
]

export default _nav
