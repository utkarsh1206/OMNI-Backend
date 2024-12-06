import React from 'react'
import { CCard, CCardBody, CCardHeader, CCardTitle, CButton } from '@coreui/react'

const Transfers = () => {
  return (
    <CCard>
      <CCardHeader>
        <CCardTitle>Transfers</CCardTitle>
      </CCardHeader>
      <CCardBody>
        <div className="d-grid gap-2">
          <CButton color="primary">Transfer Ticket</CButton>
        </div>
        <hr />
        <h5>Transferred Ticket Logs</h5>
        Transferred To: <strong>&lt;Name of new assignee&gt;</strong>
        <br />
        Transferred Time: <strong>&lt;Time&gt;</strong>
        <br />
      </CCardBody>
    </CCard>
  )
}

export default Transfers
