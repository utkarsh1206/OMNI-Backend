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

  const isUser = user.role_id == 3

  console.log('roles ', user.role_id)
  console.log('isUser', isUser)

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

  // Define custom filter card for UserGroups
  const renderTicketFilters = (filters, setFilters) => (
    <>
      <div>
        <label>
          SLA Name:
          <input
            type="text"
            name="name"
            value={filters.name || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, name: e.target.value }))}
            placeholder="Enter name"
          />
        </label>
      </div>
      <div>
        <label>
          start_time:
          <CFormInput
            type="time"
            value={filters.start_time || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, start_time: e.target.value }))}
          />
        </label>
      </div>
      <div>
        <label>
          end_time:
          <CFormInput
            type="time"
            value={filters.end_time || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, end_time: e.target.value }))}
          />
        </label>
      </div>
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
                filters={filters} // Current filters
                setFilters={setFilters} // Update filters
                renderFilters={renderTicketFilters} // Custom filter UI for users
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
                  {/* <CTableDataCell className="align-content-center">{sla.id}</CTableDataCell> */}
                  <CTableDataCell
                    className="align-content-center"
                    // onClick={() => handleTicketModal(sla.id)}
                  >
                    {sla.id}

                    {/* <CButton color="link" onClick={() => navigate(`/spprt_tckt_view`)}>
                      {sla.id}
                    </CButton> */}
                  </CTableDataCell>
                  {/* Ticket No will be generated later */}
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

                      <CTooltip content="Mail">
                        <CButton
                          color="secondary"
                          size="sm"
                          // onClick={() => handleMailTicket(sla.id)}
                        >
                          <CIcon icon={cilEnvelopeOpen} />
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

          {/* Reusable PaginationComponent */}
          <PaginationComponent
            apiEndpoint={`${BASE_URL}/api/sla-settings`}
            authToken={authToken}
            onDataFetched={setSla}
            filters={filters}
            search={searchQuery}
            // onPageChange={handlePageChange}
            // perPageValue={handlePerPageValue}
          />
        </CCol>
      </CRow>
    </>
  )
}

export default SLAConfiguration
