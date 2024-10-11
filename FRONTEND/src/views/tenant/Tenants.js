import React, { useEffect, useState } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CCol,
  CContainer,
  CFormCheck,
  CRow,
  CTable,
  CTableBody,
  CTableDataCell,
  CTableHead,
  CTableHeaderCell,
  CTableRow,
} from '@coreui/react'
import axios from 'axios'
import { Link } from 'react-router-dom'
import UserModal from '../user/UserModal'
import { BASE_URL } from '../../config'

const TenantsTable = () => {
  const [tenants, setTenants] = useState([])

  useEffect(() => {
    axios
      .get(`${BASE_URL}/api/tenants`)
      .then((response) => {
        setTenants(response.data.data)
      })
      .catch((error) => {
        console.error('There was an error fetching the posts!', error)
      })
  }, [])
  return { tenants }
}

const Tenants = () => {
  const { tenants } = TenantsTable()

  const linkStyle = {
    textDecoration: 'none',
    color: 'inherit',
  }

  const [visible, setVisible] = useState(false)
  const [modalType, setModalType] = useState('')

  const handleCreateTenant = () => {
    setModalType('create')
    setVisible(true)
  }

  const handleDeleteUser = () => {
    setModalType('delete')
    setVisible(true)
  }

  function formatDate(string){
    var options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(string).toLocaleDateString([],options);
}

  return (
    <>
      <CRow>
        <CCol xs={12}>
          <CCard className="mb-2">
            <CCardHeader>
              Search for <strong>Tenants</strong> <small>Filters will be filled later</small>
            </CCardHeader>
            <CCardBody>Placeholder for tenant search fields</CCardBody>
          </CCard>
        </CCol>
      </CRow>
      <hr />
      <CRow>
        <CCol>
          <CContainer>
            <CRow fluid responsive>
              <CCol md={8}>
                {/* <CButton color="primary" className="mb-4 me-2" size="sm" onClick={handleCreateTenant}>
                  Create Tenants
                </CButton> */}
                <Link to="/tenants/crt_tenant">
                  <CButton color="primary" className="mb-4 me-2" size="sm">
                    Create Tenants
                  </CButton>
                </Link>
                <CButton color="primary" className="mb-4 me-2" size="sm" onClick={handleDeleteUser}>
                  Delete Tenant
                </CButton>
                <UserModal visible={visible} setVisible={setVisible} type={modalType} />
                <CButton color="primary" className="mb-4 me-2" size="sm">
                  Download CSV
                </CButton>
              </CCol>
            </CRow>
          </CContainer>
          <CTable hover bordered responsive="md">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell>
                <CTableHeaderCell scope="col">Name</CTableHeaderCell> {/* company name */}
                <CTableHeaderCell scope="col">Email</CTableHeaderCell>
                <CTableHeaderCell scope="col">Phone Number</CTableHeaderCell>
                <CTableHeaderCell scope="col">Domain</CTableHeaderCell>
                <CTableHeaderCell scope="col">Company Code</CTableHeaderCell>
                <CTableHeaderCell scope="col">Registration Date</CTableHeaderCell>{' '}
                {/* created_at */}
                <CTableHeaderCell scope="col">Controls</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {tenants.map((tenant) => (
                <CTableRow key={tenant.id}>
                  <CTableDataCell>{tenant.id}</CTableDataCell>
                  <CTableDataCell>{tenant.company_name}</CTableDataCell>
                  <CTableDataCell>{tenant.email}</CTableDataCell>
                  <CTableDataCell>{tenant.mobile}</CTableDataCell>
                  <CTableDataCell>{tenant.domain_name}</CTableDataCell>
                  <CTableDataCell>{tenant.company_code}</CTableDataCell>
                  <CTableDataCell>{formatDate(tenant.created_at)}</CTableDataCell>
                  <CTableDataCell>Buttons</CTableDataCell>
                </CTableRow>
              ))}
            </CTableBody>
          </CTable>
        </CCol>
      </CRow>
    </>
  )
}

export default Tenants
