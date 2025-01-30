import { CButton, CCol } from '@coreui/react'
import React, { Component } from 'react'
import { Link } from 'react-router-dom'

const SuperAdmin = () => {
  return (
    <>
      <CCol>
        <h3>Super Admin Dashboard</h3>
        <div>SuperAdmin</div>
      </CCol>
      <CCol className="text-end">
        <Link to={'/spprt_tckt/crt_tckt'}>
          <CButton color="warning">Add Ticket</CButton>
        </Link>
      </CCol>
    </>
  )
}

export default SuperAdmin
