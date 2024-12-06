import React from 'react'
import { CCard, CCardBody, CCardHeader, CCardTitle, CButton } from '@coreui/react'

const MergeTickets = () => {
  return (
    <CCard>
      <CCardHeader>
        <CCardTitle>Merge Tickets</CCardTitle>
      </CCardHeader>
      <CCardBody>
        <div className="d-grid gap-2">
          <CButton color="primary">Merge Ticket</CButton>
        </div>
        <hr />
        <h5>Merged Ticket Logs</h5>
        Merged with: <strong>&lt;Ticket Number&gt;</strong>
        <br />
        Merged Time: <strong>&lt;Time&gt;</strong>
        <br />
        Merge Note: <strong>&lt;Note&gt;</strong>
        <br />
      </CCardBody>
    </CCard>
  )
}

export default MergeTickets
