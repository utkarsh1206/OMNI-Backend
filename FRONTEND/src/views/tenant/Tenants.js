import React, { useContext, useState } from 'react'
import {
  CButton,
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
import { cilPencil, cilXCircle, cilLockLocked } from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../context/AuthContext'
import PaginationComponent from '../../components/PaginationComponent' // import the reusable pagination component
import { formatDate } from '../../utils/DateUtils'
import axios from 'axios'
import SearchAndFilter from '../../components/SearchAndFilter'

const Tenants = () => {
  const navigate = useNavigate()
  const [tenants, setTenants] = useState([])
  const { authToken } = useContext(AuthContext)
  const [searchQuery, setSearchQuery] = useState('')

  const initialFilters = {
    company_name: '',
    email: '',
    mobile: '',
    domain_name: '',
    company_code: '',
  }
  const [filters, setFilters] = useState(initialFilters)

  const [currentPage, setCurrentPage] = useState(1) // Add state to track the current page
  const [perPage, setPerPage] = useState(10) // Add state to track the current page
  const [appliedFilters, setAppliedFilters] = useState({})

  const handleApplyFilters = (newFilters) => {
    setAppliedFilters(newFilters) // Update applied filters only on button click
  }
  const handleResetFilters = () => {
    setFilters(initialFilters) // Clear filter state
    setAppliedFilters({}) // Clear applied filters
    setSearchQuery('') // Clear live search
  }

  const handlePageChange = (page) => {
    setCurrentPage(page) // Update the current page when pagination changes
  }
  const handlePerPageValue = (perPage) => {
    setPerPage(perPage) // Update the current page when pagination changes
  }

  // const handleFilterChange = (e) => {
  //   const { name, value } = e.target
  //   setFilters((prevFilters) => ({
  //     ...prevFilters,
  //     [name]: value, // Dynamically update the property based on input name
  //   }))
  // }

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
        alert(error.response.data.message)
      }
    }
  }

  const renderTenantFilters = (filters, setFilters) => (
    <>
      <CRow>
        <CCol xs={12} md={3} className="mb-2">
          <label htmlFor="company_name" className="form-label">
            Tenant Name:
          </label>
          <CFormInput
            id="company_name"
            type="text"
            name="company_name"
            value={filters.company_name || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, company_name: e.target.value }))}
            placeholder="Enter Tenant Name"
          />
        </CCol>
        <CCol xs={12} md={3} className="mb-2">
          <label htmlFor="email" className="form-label">
            Tenant Email:
          </label>
          <CFormInput
            id="email"
            type="text"
            name="email"
            value={filters.email || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, email: e.target.value }))}
            placeholder="Enter email"
          />
        </CCol>
        <CCol xs={12} md={3} className="mb-2">
          <label htmlFor="mobile" className="form-label">
            Mobile:
          </label>
          <CFormInput
            id="mobile"
            type="text"
            name="mobile"
            value={filters.mobile || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, mobile: e.target.value }))}
            placeholder="Enter mobile"
          />
        </CCol>
        <CCol xs={12} md={3} className="mb-2">
          <label htmlFor="domain_name" className="form-label">
            Domain:
          </label>
          <CFormInput
            id="domain_name"
            type="text"
            name="domain_name"
            value={filters.domain_name || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, domain_name: e.target.value }))}
            placeholder="Enter domain_name"
          />
        </CCol>
        <CCol xs={12} md={3} className="mb-2">
          <label htmlFor="company_code" className="form-label">
            Company Code:
          </label>
          <CFormInput
            id="company_code"
            type="text"
            name="company_code"
            value={filters.company_code || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, company_code: e.target.value }))}
            placeholder="Enter company_code"
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
            <CRow>
              <CCol md={8}>
                <Link to="/tenants/crt_tenant">
                  <CButton color="primary" className="me-2" size="sm">
                    Create Tenant
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
                initialFilters={initialFilters}
              />
            </CRow>
          </CContainer>
          <hr />
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
              {tenants.map((tenant, index) => (
                <CTableRow key={tenant.id}>
                  <CTableDataCell>{index + 1 + (currentPage - 1) * perPage}</CTableDataCell>
                  {/* <CTableDataCell>{tenant.id}</CTableDataCell> */}
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
