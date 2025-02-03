// export const BASE_URL = 'http://127.0.0.1:8000'
export const BASE_URL = 'http://192.168.0.131/omni/BACKEND/public'

export const NEOX_MAIN_COLOR = '#3653A4'
export const NEOX_MAIN_COLOR_GREY = '#D5D5EA'
export const NEOX_MAIN_COLOR_LIGHT = '#91D3DF'
export const NEOX_MAIN_COLOR_PURPLE = '#BAA1CD'

export const TEXT_GREEN = '#39ff14'
export const TEXT_RED = '#ff3131'

export const TYPE_TO_COLOR = {
  Problems: 'success', // green
  Help: 'danger', // red
  Incident: 'primary', // blue
  'Service Request': 'warning', // yellow
  'Service Delivery': 'info', // light blue
  'Service Assurance': 'secondary', // grey
  Questions: 'dark', // black
  'Feature Request': 'light', // light grey
  Other: 'transparent', // transparent
  'Change Management': 'secondary', // grey
}

export const PRIORITY_TO_COLOR = {
  Low: 'info',
  Normal: 'primary',
  Medium: 'success',
  High: 'warning',
  Critical: 'danger',
}

export const STATUS_TO_COLOR = {
  Unassigned: 'info',
  Open: 'danger',
  'Waiting on Customer': 'warning',
  'Waiting on Third Party': 'warning',
  Resolved: 'success',
  Closed: 'success',
}

export const CAMPAIGNS = [
  { id: '1', name: 'Camp_1' },
  { id: '2', name: 'Camp_2' },
  { id: '3', name: 'Camp_3' },
  { id: '4', name: 'Camp_4' },
  { id: '5', name: 'Camp_5' },
  { id: '6', name: 'Camp_6' },
  // Add more campaigns as needed
]

export const PRODUCTS = [
  { id: '1', name: 'Hosted' },
  { id: '2', name: 'Neox' },
  { id: '3', name: 'OMNI' },
  // Add more Products as needed
]
