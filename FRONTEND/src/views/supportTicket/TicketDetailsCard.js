import React from 'react'
import {
  CBadge,
  CCard,
  CCardBody,
  CCardHeader,
  CCardTitle,
  CCol,
  CRow,
  CSpinner,
} from '@coreui/react'
import { Link } from 'react-router-dom'
import { cilBuilding, cilMap, cilMobile } from '@coreui/icons'
import CIcon, { CIconSvg } from '@coreui/icons-react'
import { formatDateWithTime } from '../../utils/DateUtils'
import { PRIORITY_TO_COLOR, STATUS_TO_COLOR, TYPE_TO_COLOR } from '../../config'

const TicketDetailsCard = ({ ticket, account, contact }) => {
  if (!ticket || !ticket.id)
    return (
      <CCard className="mb-3">
        <CCardBody>
          <div className="text-center">
            <CSpinner color="primary" />
            <p>Loading ticket data...</p>
          </div>
        </CCardBody>
      </CCard>
    ) // Avoid rendering until data is loaded

  return (
    <CCard className="mb-3">
      <CCardHeader>
        <CCardTitle>
          Support Ticket Details - <small>{ticket.assign_to_id} (Assigned to username)</small> -{' '}
          <CBadge color={ticket.default_status === 'Unassigned' ? 'warning' : 'success'}>
            {ticket.default_status === 'Unassigned' ? 'Unassigned' : 'Assigned'}
          </CBadge>
        </CCardTitle>
      </CCardHeader>
      <CCardBody>
        <CRow>
          <CCol sm={2} md={3} className="align-content-center text-center">
            {/* {!brandingInfo?.tenant_logo ? (
                      <> */}
            <CIconSvg size="9xl">
              {/* <svg width="800px" height="800px" viewBox="0 0 1024 1024" class="icon"  version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M512 661.3c-117.6 0-213.3-95.7-213.3-213.3S394.4 234.7 512 234.7 725.3 330.4 725.3 448 629.6 661.3 512 661.3z m0-341.3c-70.6 0-128 57.4-128 128s57.4 128 128 128 128-57.4 128-128-57.4-128-128-128z" fill="#5F6379" /><path d="M837 862.9c-15.7 0-30.8-8.7-38.2-23.7C744.3 729.5 634.4 661.3 512 661.3s-232.3 68.1-286.8 177.9c-10.5 21.1-36.1 29.7-57.2 19.2s-29.7-36.1-19.2-57.2C217.8 662.3 357 576 512 576s294.2 86.3 363.2 225.2c10.5 21.1 1.9 46.7-19.2 57.2-6.1 3-12.6 4.5-19 4.5z" fill="#5F6379" /><path d="M512 1002.7c-270.6 0-490.7-220.1-490.7-490.7S241.4 21.3 512 21.3s490.7 220.1 490.7 490.7-220.1 490.7-490.7 490.7z m0-896c-223.5 0-405.3 181.8-405.3 405.3S288.5 917.3 512 917.3 917.3 735.5 917.3 512 735.5 106.7 512 106.7z" fill="#3688FF" /></svg> */}
              <svg fill="#000000" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                <g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g>
                <g id="SVGRepo_iconCarrier">
                  <path d="M15 6c-1.55 0-2.814.786-3.666 1.78C10.482 8.778 10 9.975 10 11v2.5c0 .667.334 1.152.584 1.527.25.375.416.64.416.973v1c0 .61-.062 1.174-.748 1.566l-3.5 2c-.8.457-1.272.9-1.518 1.416C4.99 22.498 5 23.002 5 23.5c0 .67 1 .66 1 0 0-.5.008-.818.137-1.088.128-.27.406-.575 1.11-.978l3.5-2C11.813 18.826 12 17.734 12 17v-1c0-.667-.334-1.152-.584-1.527-.25-.375-.416-.64-.416-.973V11c0-.69.387-1.743 1.094-2.568C12.8 7.606 13.784 7 15 7c1.215 0 2.2.606 2.906 1.432C18.613 9.257 19 10.312 19 11v2.5c0 .333-.166.598-.416.973-.25.375-.584.86-.584 1.527v1c0 .735.188 1.826 1.252 2.434l3.5 2c.705.403.983.708 1.11.978.13.27.138.587.138 1.088 0 .67 1 .672 1 0 0-.5.01-1.002-.234-1.518-.246-.515-.72-.96-1.518-1.416l-3.5-2C19.062 18.174 19 17.61 19 17v-1c0-.333.166-.598.416-.973.25-.375.584-.86.584-1.527V11c0-1.026-.482-2.223-1.334-3.22C17.814 6.787 16.55 6 15 6zm0-6C6.722 0 0 6.722 0 15c0 8.278 6.722 15 15 15 8.278 0 15-6.722 15-15 0-8.278-6.722-15-15-15zm0 1c7.738 0 14 6.262 14 14s-6.262 14-14 14S1 22.738 1 15 7.262 1 15 1z"></path>
                </g>
              </svg>
            </CIconSvg>
            {/* </>
                    ) : (
                      <CImage src={brandingInfo?.tenant_logo}></CImage>
                      // Will Ideally Be the Account Logo
                    )} */}
          </CCol>
          <CCol>
            <h6>
              #{ticket.id} - {formatDateWithTime(ticket.created_at)}
            </h6>
            <h5>
              <Link to="#">
                {ticket.contact_id} - {contact.name_prefix} {contact.first_name} {contact.last_name}
              </Link>
            </h5>
            <p>
              <CIcon icon={cilBuilding} size="lg" />{' '}
              <Link to="#">
                <strong>
                  {ticket.account_id} {account.name}
                </strong>
              </Link>
            </p>
            <p>
              <CIcon icon={cilMobile} size="lg" /> {contact.mobile}
            </p>
            <p>
              <CIcon icon={cilMap} size="lg" /> {contact.address}
            </p>
            <strong>Subject: </strong>
            {ticket.subject}

            <CRow className="my-3">
              <CCol sm={4}>
                <strong>Type: </strong>{' '}
                <CBadge color={TYPE_TO_COLOR[ticket.default_type] || 'secondary'}>
                  {ticket.default_type}
                </CBadge>
              </CCol>
              <CCol sm={4}>
                <strong>Priority: </strong>{' '}
                <CBadge color={PRIORITY_TO_COLOR[ticket.default_priority] || 'secondary'}>
                  {ticket.default_priority}
                </CBadge>
              </CCol>
              <CCol sm={4}>
                <strong>Status: </strong>{' '}
                <CBadge color={STATUS_TO_COLOR[ticket.default_status] || 'secondary'}>
                  {ticket.default_status}
                </CBadge>
              </CCol>
            </CRow>
          </CCol>
        </CRow>
        {ticket.attachment && (
          <CRow className="mb-4">
            <h6>Attachments:</h6>
          </CRow>
        )}
      </CCardBody>
    </CCard>
  )
}

export default TicketDetailsCard
