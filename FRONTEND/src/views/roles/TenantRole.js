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
import { cilPencil, cilXCircle } from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../context/AuthContext'
import PaginationComponent from '../../components/PaginationComponent' // import the reusable pagination component
import { formatDate } from '../../utils/DateUtils'
import axios from 'axios'

const Tenants = () => {
  const navigate = useNavigate()
  const [roles, setRoles] = useState([])
  const { authToken } = useContext(AuthContext)

  const handleDeleteRole = async (id, name) => {
    if (window.confirm('Are you sure to delete ' + name + '?')) {
      try {
        let response = await axios.delete(`${BASE_URL}/api/tenantrole/${id}`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          // Optionally, refetch the data
          setRoles((prev) => prev.filter((role) => role.id !== id)) // Immediately update the UI
        }
      } catch (error) {
        console.error('Operation failed:', error)
        alert('An error occurred while processing your request.')
      }
    }
  }

  // Format date
  //   const formatDate = (string) => {
  //     const options = { year: 'numeric', month: 'long', day: 'numeric' }
  //     return new Date(string).toLocaleDateString([], options)
  //   }

  return (
    <>
      <CRow>
        <CCol xs={12}>
          <CCard className="mb-2">
            <CCardHeader>
              Search for <strong>Role</strong> <small>Filters will be added later</small>
            </CCardHeader>
            <CCardBody>
              Placeholder for Role search fields
              <CRow className="mt-3">
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
                <Link to="/tenants/tenant_role/crt_tenant_role">
                  <CButton color="primary" className="mb-4 me-2" size="sm">
                    Create Role
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
                <CTableHeaderCell scope="col">Description</CTableHeaderCell>
                <CTableHeaderCell scope="col">Status</CTableHeaderCell>
                <CTableHeaderCell scope="col">Created On</CTableHeaderCell>
                <CTableHeaderCell scope="col">Controls</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {roles.map((role) => (
                <CTableRow key={role.id}>
                  <CTableDataCell>{role.id}</CTableDataCell>
                  <CTableDataCell>{role.name}</CTableDataCell>
                  <CTableDataCell>{role.description}</CTableDataCell>
                  <CTableDataCell>{role.status}</CTableDataCell>
                  <CTableDataCell>{formatDate(role.created_at)}</CTableDataCell>
                  <CTableDataCell>
                    <CRow className="justify-content-center mx-0">
                      <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="Edit">
                          <CButton
                            color="warning"
                            size="sm"
                            onClick={() => navigate(`/tenants/tenant_role/edit/${role.id}`)}
                          >
                            <CIcon icon={cilPencil} />
                          </CButton>
                        </CTooltip>
                      </CCol>
                      <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="Delete">
                          <CButton
                            color="danger"
                            size="sm"
                            onClick={() => handleDeleteRole(role.id, role.name)}
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
            apiEndpoint={`${BASE_URL}/api/tenantrole`}
            authToken={authToken}
            onDataFetched={setRoles}
            // params={[testing]}
          />
        </CCol>
      </CRow>
    </>
  )
}

export default Tenants
