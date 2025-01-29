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
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { BASE_URL } from '../../../config'
import { cilPencil, cilXCircle } from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../../context/AuthContext'
import PaginationComponent from '../../../components/PaginationComponent' // import the reusable pagination component
import axios from 'axios'
import SearchAndFilter from '../../../components/SearchAndFilter'

const CRONConfig = () => {
  const navigate = useNavigate()
  const [cronConfigs, setCronConfigs] = useState([])
  const { authToken } = useContext(AuthContext)
  const [searchQuery, setSearchQuery] = useState('')

  const [filters, setFilters] = useState({
    command_signature: '',
    schedule_type: '',
    status: '',
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

  const handleFilterChange = (e) => {
    const { name, value } = e.target
    setFilters((prevFilters) => ({
      ...prevFilters,
      [name]: value, // Dynamically update the property based on input name
    }))
  }

  const handleDeleteConfig = async (id, username) => {
    if (window.confirm('Are you sure to delete ' + username + '?')) {
      try {
        let response = await axios.delete(`${BASE_URL}/api/cron-configurations/${id}`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          // Optionally, refetch the data
          setCronConfigs((prev) => prev.filter((tenant) => tenant.id !== id)) // Immediately update the UI
        }
      } catch (error) {
        console.error('Operation failed:', error)
        alert('An error occurred while processing your request.')
      }
    }
  }

  const renderCronConfigFilters = (filters, setFilters) => (
    <CRow className="mb-3">
      <CCol xs={12} md={4}>
        <label htmlFor="command_signature" className="form-label">
          Command Signature:
        </label>
        <CFormInput
          id="command_signature"
          type="text"
          name="command_signature"
          value={filters.command_signature || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, command_signature: e.target.value }))}
          placeholder="Enter Signature"
        />
      </CCol>
      <CCol xs={12} md={4}>
        <label htmlFor="schedule_type" className="form-label">
          Schedule Type:
        </label>
        <CFormInput
          id="schedule_type"
          type="text"
          name="schedule_type"
          value={filters.schedule_type || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, schedule_type: e.target.value }))}
          placeholder="Enter Schedule Type"
        />
      </CCol>
      <CCol xs={12} md={4}>
        <label htmlFor="status" className="form-label">
          Status:
        </label>
        <CFormInput
          id="status"
          type="text"
          name="status"
          value={filters.status || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
          placeholder="Enter status"
        />
      </CCol>
    </CRow>
  )

  return (
    <>
      <CRow>
        <CCol>
          <CContainer>
            <CRow fluid responsive>
              <CCol md={8}>
                <Link to={`/settings/CRON_configs/crt_cron`}>
                  <CButton color="primary" className="me-2" size="sm">
                    Configure
                  </CButton>
                </Link>
              </CCol>
              <SearchAndFilter
                onSearch={setSearchQuery} // Callback for live search
                onApplyFilters={handleApplyFilters}
                onResetFilters={handleResetFilters} // Callback for reset filters
                filters={filters} // Current filters
                setFilters={setFilters} // Update filters
                renderFilters={renderCronConfigFilters} // Custom filter UI for users
              />
            </CRow>
            <hr />
          </CContainer>
          <CTable hover bordered responsive="md">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell>
                <CTableHeaderCell scope="col">Command</CTableHeaderCell>
                <CTableHeaderCell scope="col">Schedule type</CTableHeaderCell>
                <CTableHeaderCell scope="col">Expression</CTableHeaderCell>
                {/* <CTableHeaderCell scope="col">Status</CTableHeaderCell> */}
                <CTableHeaderCell scope="col">Controls</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {cronConfigs.map((cronConfig) => (
                <CTableRow key={cronConfig.id}>
                  <CTableDataCell>{cronConfig.id}</CTableDataCell>
                  <CTableDataCell>{cronConfig.command_signature}</CTableDataCell>
                  <CTableDataCell>{cronConfig.schedule_type}</CTableDataCell>
                  <CTableDataCell>{cronConfig.schedule_expression}</CTableDataCell>
                  {/* <CTableDataCell>{cronConfig.status}</CTableDataCell> */}
                  <CTableDataCell>
                    <CRow className="justify-content-center mx-0">
                      <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="Edit">
                          <CButton
                            color="warning"
                            size="sm"
                            onClick={() => navigate(`/settings/CRON_configs/edit/${cronConfig.id}`)}
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
                            onClick={() => handleDeleteConfig(cronConfig.id, '')}
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
            apiEndpoint={`${BASE_URL}/api/cron-configurations`}
            authToken={authToken}
            onDataFetched={setCronConfigs}
            filters={appliedFilters}
            search={searchQuery}
            onPageChange={handlePageChange}
            perPageValue={handlePerPageValue}
          />
        </CCol>
      </CRow>
    </>
  )
}

export default CRONConfig
