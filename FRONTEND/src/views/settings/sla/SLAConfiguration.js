import React, { useContext, useState } from 'react'
import {
  CBadge,
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
import { BASE_URL } from '../../../config'
import { cilEnvelopeOpen, cilPencil, cilXCircle } from '@coreui/icons'
import EyeOpenIcon from '../../../assets/images/icons/eye_open.svg'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../../context/AuthContext'
import PaginationComponent from '../../../components/PaginationComponent'
import axios from 'axios'
import SearchAndFilter from '../../../components/SearchAndFilter'

const SLAConfiguration = () => {
  const navigate = useNavigate()
  const [sla, setSla] = useState([])
  const { authToken, user } = useContext(AuthContext)

  const [searchQuery, setSearchQuery] = useState('')
  const [filters, setFilters] = useState({
    name: '',
    start_time: '',
    end_time: '',
  })

  const [appliedFilters, setAppliedFilters] = useState({})

  const isUser = user.role_id == 3

  const handleApplyFilters = (newFilters) => {
    setAppliedFilters(newFilters) // Update applied filters only on button click
  }

  const handleResetFilters = () => {
    setFilters({}) // Clear filter state
    setAppliedFilters({}) // Clear applied filters
    setSearchQuery('') // Clear live search
  }

  const handleDeleteTicket = async (id, username) => {
    if (window.confirm('Are you sure to delete ' + username + '?')) {
      try {
        let response = await axios.delete(`${BASE_URL}/api/sla-settings/${id}`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          // Optionally, refetch the data
          setSla((prev) => prev.filter((tenant) => tenant.id !== id)) // Immediately update the UI
        }
      } catch (error) {
        console.error('Operation failed:', error)
        alert('An error occurred while processing your request.')
      }
    }
  }

  // const handleFilterApply = (appliedFilters) => {
  //   setFilters(appliedFilters) // Update the filters
  //   setIsFilterApplied(true) // Mark that filters are applied
  // }

  // Define custom filter card for UserGroups
  const renderTicketFilters = (filters, setFilters) => (
    <>
      <CRow className="mb-3">
        <CCol xs={12} md={4}>
          <label htmlFor="sla-name" className="form-label">
            SLA Name:
          </label>
          <CFormInput
            id="sla-name"
            type="text"
            name="name"
            value={filters.name || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, name: e.target.value }))}
            placeholder="Enter name"
          />
        </CCol>
        <CCol xs={12} md={3}>
          <label htmlFor="start-time" className="form-label">
            Start Time:
          </label>
          <CFormInput
            id="start-time"
            type="time"
            value={filters.start_time || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, start_time: e.target.value }))}
          />
        </CCol>
        <CCol xs={12} md={3}>
          <label htmlFor="end-time" className="form-label">
            End Time:
          </label>
          <CFormInput
            id="end-time"
            type="time"
            value={filters.end_time || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, end_time: e.target.value }))}
          />
        </CCol>
      </CRow>
    </>
  )

  return (
    <>
      <CRow>
        <CCol>
          <CContainer className="mb-3">
            <CRow fluid responsive>
              <CCol md={8}>
                {!isUser && (
                  <Link to="/settings/SLA_configuration">
                    <CButton color="primary" className="me-2" size="sm">
                      Create SLA Configuration
                    </CButton>
                  </Link>
                )}
              </CCol>
              <SearchAndFilter
                onSearch={setSearchQuery} // Callback for live search
                onApplyFilters={handleApplyFilters} // Callback for apply filters
                onResetFilters={handleResetFilters} // Callback for reset filters
                filters={filters}
                setFilters={setFilters}
                renderFilters={renderTicketFilters}
              />
            </CRow>
          </CContainer>
          <hr />
          <CTable hover small bordered responsive="md">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell>
                <CTableHeaderCell scope="col">SLA Name</CTableHeaderCell>
                <CTableHeaderCell scope="col">SLA Description</CTableHeaderCell>
                <CTableHeaderCell scope="col">24 Hours</CTableHeaderCell>
                <CTableHeaderCell scope="col">Actions</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {sla.map((sla) => (
                <CTableRow key={sla.id}>
                  <CTableDataCell className="align-content-center">{sla.id}</CTableDataCell>
                  <CTableDataCell className="align-content-center">{sla.name}</CTableDataCell>
                  <CTableDataCell className="align-content-center">
                    {sla.description}
                  </CTableDataCell>
                  <CTableDataCell className="align-content-center">
                    <CBadge color={sla.is_24_hours == 1 ? 'success' : 'danger'}>
                      {sla.is_24_hours == 1 ? 'True' : 'False'}
                    </CBadge>
                  </CTableDataCell>

                  <CTableDataCell className="align-content-center">
                    <div style={{ display: 'flex', justifyContent: 'center', gap: '8px' }}>
                      <CTooltip content="Edit">
                        <CButton
                          color="warning"
                          size="sm"
                          onClick={() => navigate(`/settings/SLA_configuration/edit/${sla.id}`)}
                        >
                          <CIcon icon={cilPencil} />
                        </CButton>
                      </CTooltip>

                      <CTooltip content="View">
                        <CButton color="secondary" size="sm">
                          {/* <CIcon icon={cilEnvelopeOpen} /> */}
                          <img src={EyeOpenIcon} height={18} width={18} />
                        </CButton>
                      </CTooltip>
                      {!isUser && (
                        <CTooltip content="Delete">
                          <CButton
                            color="danger"
                            size="sm"
                            onClick={() => handleDeleteTicket(sla.id, sla.username)}
                          >
                            <CIcon icon={cilXCircle} />
                          </CButton>
                        </CTooltip>
                      )}
                    </div>
                  </CTableDataCell>
                </CTableRow>
              ))}
            </CTableBody>
          </CTable>

          <PaginationComponent
            apiEndpoint={`${BASE_URL}/api/sla-settings`}
            authToken={authToken}
            onDataFetched={setSla}
            filters={appliedFilters} // Use applied filters
            search={searchQuery} // Use live search
            // onPageChange={handlePageChange}
            // perPageValue={handlePerPageValue}

            // filters={filters}
            // search={searchQuery}
            // filters={isFilterApplied ? filters : {}} // Use filters only if applied
            // search={!isFilterApplied ? searchQuery : ''} // Use search only if filters are not applied
          />
        </CCol>
      </CRow>
    </>
  )
}

export default SLAConfiguration
