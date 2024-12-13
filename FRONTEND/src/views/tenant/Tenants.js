import React, { useContext, useState } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CCol,
  CContainer,
  CRow,
  CTable,
  CTableBody,
  CTableDataCell,
  CTableHead,
  CTableHeaderCell,
  CTableRow,
  CTooltip,
} from '@coreui/react'
import { Link, useNavigate } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { cilPencil, cilXCircle, cilLockLocked } from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../context/AuthContext'
import PaginationComponent from '../../components/PaginationComponent' // import the reusable pagination component
import { formatDate } from '../../utils/DateUtils'
import axios from 'axios'

const Tenants = () => {
  const navigate = useNavigate()
  const [tenants, setTenants] = useState([])
  const { authToken } = useContext(AuthContext)

  const [filters, setFilters] = useState({
    category: '',
    status: '',

    // Add more filters here as needed
  })

  const handleFilterChange = (e) => {
    const { name, value } = e.target
    setFilters((prevFilters) => ({
      ...prevFilters,
      [name]: value, // Dynamically update the property based on input name
    }))
  }

  const handleDeleteTenant = async (id, username) => {
    if (window.confirm('Are you sure to delete ' + username + '?')) {
      try {
        let response = await axios.delete(`${BASE_URL}/api/tenants/${id}`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          // Optionally, refetch the data
          setTenants((prev) => prev.filter((tenant) => tenant.id !== id)) // Immediately update the UI
        }
      } catch (error) {
        console.error('Operation failed:', error)
        alert('An error occurred while processing your request.')
      }
    }
  }

  return (
    <>
      <CRow>
        <CCol xs={12}>
          <CCard className="mb-2">
            <CCardHeader>
              Search for <strong>Tenants</strong> <small>Filters will be filled later</small>
            </CCardHeader>
            <CCardBody>
              Placeholder for tenant search fields
              <CRow className="mt-3">
                <CCol>
                  <input type="datetime-local" className="form-control" />
                </CCol>
                <CCol>
                  <CButton color="primary" onClick={() => fetchTenants(1)}>
                    Search
                  </CButton>
                </CCol>
              </CRow>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>
      <hr />
      <CRow>
        <CCol>
          <CContainer>
            <CRow fluid responsive>
              <CCol md={8}>
                <Link to="/tenants/crt_tenant">
                  <CButton color="primary" className="mb-4 me-2" size="sm">
                    Create Tenant
                  </CButton>
                </Link>
              </CCol>
            </CRow>
          </CContainer>
          <CTable hover bordered responsive="md">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell>
                <CTableHeaderCell scope="col">Name</CTableHeaderCell>
                <CTableHeaderCell scope="col">Email</CTableHeaderCell>
                <CTableHeaderCell scope="col">Phone Number</CTableHeaderCell>
                <CTableHeaderCell scope="col">Domain</CTableHeaderCell>
                <CTableHeaderCell scope="col">Company Code</CTableHeaderCell>
                <CTableHeaderCell scope="col">Registration Date</CTableHeaderCell>
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
                  <CTableDataCell>
                    <CRow className="justify-content-center mx-0">
                      <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="Edit">
                          <CButton
                            color="warning"
                            size="sm"
                            onClick={() => navigate(`/tenants/edit/${tenant.id}`)}
                          >
                            <CIcon icon={cilPencil} />
                          </CButton>
                        </CTooltip>
                      </CCol>
                      <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="License">
                          <CButton
                            color="primary"
                            size="sm"
                            onClick={() => navigate(`/tenant_license/${tenant.id}`)}
                          >
                            <CIcon icon={cilLockLocked} />
                          </CButton>
                        </CTooltip>
                      </CCol>
                      <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="Delete">
                          <CButton
                            color="danger"
                            size="sm"
                            onClick={() => handleDeleteTenant(tenant.id, tenant.username)}
                          >
                            <CIcon icon={cilXCircle} />
                          </CButton>
                        </CTooltip>
                      </CCol>
                    </CRow>
                  </CTableDataCell>
                </CTableRow>
              ))}
            </CTableBody>
          </CTable>

          {/* Reusable PaginationComponent */}
          <PaginationComponent
            apiEndpoint={`${BASE_URL}/api/tenants`}
            authToken={authToken}
            onDataFetched={setTenants}
            filters={filters}
          />
        </CCol>
      </CRow>
    </>
  )
}

export default Tenants
