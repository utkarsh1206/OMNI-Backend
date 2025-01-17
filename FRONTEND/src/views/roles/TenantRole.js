import React, { useContext, useState } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CCol,
  CContainer,
  CFormInput,
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
import SearchAndFilter from '../../components/SearchAndFilter'

const Tenants = () => {
  const navigate = useNavigate()
  const [roles, setRoles] = useState([])
  const { authToken } = useContext(AuthContext)
  const [searchQuery, setSearchQuery] = useState('')
  const [filters, setFilters] = useState({
    name: '',
    description: '',
    // Add more filters here as needed
  })

  const [currentPage, setCurrentPage] = useState(1) // Add state to track the current page
  const [perPage, setPerPage] = useState(10) // Add state to track the current page
  const [appliedFilters, setAppliedFilters] = useState({})

  const handleApplyFilters = (newFilters) => {
    setAppliedFilters(newFilters) // Update applied filters only on button click
  }
  const handleResetFilters = () => {
    setFilters({}) // Clear filter state
    setAppliedFilters({}) // Clear applied filters
    setSearchQuery('') // Clear live search
  }

  const handlePageChange = (page) => {
    setCurrentPage(page) // Update the current page when pagination changes
  }
  const handlePerPageValue = (perPage) => {
    setPerPage(perPage) // Update the current page when pagination changes
  }

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

  const renderTenantFilters = (filters, setFilters) => (
    <>
      <CRow>
        <CCol xs={12} md={5} className="mb-2">
          <label htmlFor="name" className="form-label">
            Name:
          </label>
          <CFormInput
            id="name"
            type="text"
            name="name"
            value={filters.name || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, name: e.target.value }))}
            placeholder="Enter Tenant Name"
          />
        </CCol>
        <CCol xs={12} md={7} className="mb-2">
          <label htmlFor="description" className="form-label">
            Description:
          </label>
          <CFormInput
            id="description"
            type="text"
            name="description"
            value={filters.description || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, description: e.target.value }))}
            placeholder="Enter description"
          />
        </CCol>
      </CRow>
    </>
  )

  return (
    <>
      <CRow>
        <CCol>
          <CContainer>
            <CRow fluid responsive>
              <CCol md={8}>
                <Link to="/tenants/tenant_role/crt_tenant_role">
                  <CButton color="primary" className="me-2" size="sm">
                    Create Role
                  </CButton>
                </Link>
              </CCol>
              <SearchAndFilter
                onSearch={setSearchQuery} // Callback for live search
                onApplyFilters={handleApplyFilters}
                onResetFilters={handleResetFilters} // Callback for reset filters
                filters={filters} // Current filters
                setFilters={setFilters} // Update filters
                renderFilters={renderTenantFilters} // Custom filter UI for users
              />
            </CRow>
          </CContainer>
          <hr />
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
              {roles.map((role, index) => (
                <CTableRow key={role.id}>
                  {/* <CTableDataCell>{role.id}</CTableDataCell> */}
                  <CTableDataCell>{index + 1 + (currentPage - 1) * perPage}</CTableDataCell>
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
            filters={appliedFilters} // Use applied filters
            search={searchQuery} // Use live search
            onPageChange={handlePageChange}
            perPageValue={handlePerPageValue}
          />
        </CCol>
      </CRow>
    </>
  )
}

export default Tenants
