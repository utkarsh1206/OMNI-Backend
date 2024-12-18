import React from 'react'
import CIcon from '@coreui/icons-react'
import {
  cilCalculator,
  cilChart,
  cilCursor,
  cilPhone,
  cilUser,
  cilTags,
} from '@coreui/icons'
import { CNavGroup, CNavGroupItems, CNavItem, CNavTitle } from '@coreui/react'

const _nav = [
  {
    component: CNavTitle,
    name: 'Components',
  },
  {
    component: CNavItem,
    name: 'Support Tickets',
    to: '/agent/spprt_tckt',
    icon: <CIcon icon={cilTags} customClassName="nav-icon" />,
  },
  {
    component: CNavItem,
    name: 'Live Calls',
    to: '/neox_dash/live_calls',
    icon: <CIcon icon={cilPhone} customClassName="nav-icon" />,
  },
  {
    component: CNavItem,
    name: 'Live Agents',
    to: '/neox_dash/live_agents',
    icon: <CIcon icon={cilUser} customClassName="nav-icon" />,
  },
  {
    component: CNavItem,
    name: 'Campaign Details',
    to: '/neox_dash/camp_dtls',
    icon: <CIcon icon={cilCursor} customClassName="nav-icon" />,
  },
  {
    component: CNavItem,
    name: 'Ingroup Details',
    to: '/neox_dash/ingrp_dtls',
    icon: <CIcon icon={cilChart} customClassName="nav-icon" />,
  },
  {
    component: CNavItem,
    name: 'KPI Details',
    to: '/neox_dash/kpi_dtls',
    icon: <CIcon icon={cilCalculator} customClassName="nav-icon" />,
  },
]

export default _nav
