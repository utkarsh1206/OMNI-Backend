import { CButton, CCol } from '@coreui/react'
import React, { Component } from 'react'
import { Link } from 'react-router-dom'

const Agent = ({ user }) => {
  return (
    <>
      <CCol>
        <h3>{user.username} Dashboard</h3>
        <div>Agent</div>
      </CCol>
      <CCol className="text-end">
        <Link to={'/spprt_tckt/crt_tckt'}>
          <CButton color="warning">Add Ticket</CButton>
        </Link>
      </CCol>
    </>
  )
}

export default Agent
