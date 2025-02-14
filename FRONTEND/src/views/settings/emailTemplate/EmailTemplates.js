import React, { useContext, useState } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CCol,
  CContainer,
  CFormInput,
  CFormLabel,
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
import { formatDate } from '../../../utils/DateUtils'
import axios from 'axios'
import SearchAndFilter from '../../../components/SearchAndFilter'

const EmailTemplates = () => {
  const navigate = useNavigate()
  const location = useLocation()
  const [emailTemplates, setEmailTemplates] = useState([])
  const { authToken } = useContext(AuthContext)

  const [searchQuery, setSearchQuery] = useState('')
  const [filters, setFilters] = useState({
    title: '',
    subject: '',
    email_for: '',
    // Add more filters here as needed
  })

  // Extract type from query string
  const searchParams = new URLSearchParams(location.search)
  const emailType = searchParams.get('type')

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
        let response = await axios.delete(`${BASE_URL}/api/mail-templates/${id}`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          // Optionally, refetch the data
          setEmailTemplates((prev) => prev.filter((tenant) => tenant.id !== id)) // Immediately update the UI
        }
      } catch (error) {
        console.error('Operation failed:', error)
        alert('An error occurred while processing your request.')
      }
    }
  }

  // Define custom filter card for Users
  const renderTemplateFilters = (filters, setFilters) => (
    <>
      {/* <div>
        <label>
          Email:
          <input
            type="text"
            name="email"
            value={filters.email || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, email: e.target.value }))}
            placeholder="Enter email"
          />
        </label>
      </div>
      <div>
        <label>
          Status:
          <select
            name="status"
            value={filters.status || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
          >
            <option value="">Select status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </label>
      </div> */}
      <CRow className="mb-2">
        <CCol>
          <CRow className="mb-2">
            <CFormLabel htmlFor="title" className="col-sm-3 col-form-label">
              Title
            </CFormLabel>
            <CCol>
              <CFormInput
                name="title"
                type="text"
                id="title"
                value={filters.title || ''}
                onChange={handleFilterChange}
              />
            </CCol>
          </CRow>
        </CCol>
        <CCol>
          <CRow className="mb-2 align-middle">
            <CFormLabel htmlFor="subject" className="col-sm-3 col-form-label">
              Subject
            </CFormLabel>
            <CCol>
              <CFormInput
                name="subject"
                type="text"
                id="subject"
                value={filters.subject || ''}
                onChange={handleFilterChange}
              />
            </CCol>
          </CRow>
        </CCol>
        <CCol>
          <CRow className="mb-2 align-middle">
            <CFormLabel htmlFor="email_for" className="col-sm-3 col-form-label">
              Email For
            </CFormLabel>
            <CCol>
              <CFormInput
                name="email_for"
                type="text"
                id="email_for"
                value={filters.email_for || ''}
                onChange={handleFilterChange}
              />
            </CCol>
          </CRow>
        </CCol>
      </CRow>
      <div className="d-grid gap-2 d-md-flex justify-content-md-end">
        <CButton type="submit" color="primary" form="userGroupFilter">
          Submit
        </CButton>
      </div>
    </>
  )

  return (
    <>
      {/* <CRow>
        <CCol xs={12}>
          <CCard className="mb-2">
            <CCardHeader>
              Search for <strong>Email Templates</strong> <small>Filters will be filled later</small>
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
      </CRow> */}

      {/* <h5>Email Configurations for: {emailType?.toUpperCase()}</h5> */}
      <CRow>
        <CCol>
          <CContainer>
            <CRow>
              <CCol md={8}>
                <Link to={`/settings/email_templates/crt_tmplt`}>
                  <CButton color="primary" className="me-2" size="sm">
                    Create Template
                  </CButton>
                </Link>
              </CCol>
              <SearchAndFilter
                onSearch={setSearchQuery} // Callback for live search
                filters={filters} // Current filters
                setFilters={setFilters} // Update filters
                renderFilters={renderTemplateFilters} // Custom filter UI for users
              />
            </CRow>
          </CContainer>{' '}
          <hr />
          <CTable hover bordered responsive="md">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell>
                <CTableHeaderCell scope="col">Title</CTableHeaderCell>
                <CTableHeaderCell scope="col">Subject</CTableHeaderCell>
                <CTableHeaderCell scope="col">Email For</CTableHeaderCell>
                <CTableHeaderCell scope="col">Actions</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {emailTemplates.map((emailTemplate) => (
                <CTableRow key={emailTemplate.id}>
                  <CTableDataCell>{emailTemplate.id}</CTableDataCell>
                  <CTableDataCell>{emailTemplate.title}</CTableDataCell>
                  <CTableDataCell>{emailTemplate.subject}</CTableDataCell>
                  <CTableDataCell>{emailTemplate.mail_for}</CTableDataCell>
                  {/* <CTableDataCell>{formatDate(emailTemplate.created_at)}</CTableDataCell> */}
                  <CTableDataCell>
                    <CRow className="justify-content-center mx-0">
                      <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="Edit">
                          <CButton
                            color="warning"
                            size="sm"
                            onClick={() =>
                              navigate(`/settings/email_templates/edit/${emailTemplate.id}`)
                            }
                          >
                            <CIcon icon={cilPencil} />
                          </CButton>
                        </CTooltip>
                      </CCol>
                      {/* <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="Delete">
                          <CButton
                            color="danger"
                            size="sm"
                            onClick={() => handleDeleteConfig(emailTemplate.id, '')}
                          >
                            <CIcon icon={cilXCircle} />
                          </CButton>
                        </CTooltip>
                      </CCol> */}
                      {emailTemplate.is_allow === 'Yes' && (
                        <CCol className="mx-auto d-flex justify-content-center">
                          <CTooltip content="Delete">
                            <CButton
                              color="danger"
                              size="sm"
                              onClick={() => handleDeleteConfig(emailTemplate.id, '')}
                            >
                              <CIcon icon={cilXCircle} />
                            </CButton>
                          </CTooltip>
                        </CCol>
                      )}
                    </CRow>
                  </CTableDataCell>
                </CTableRow>
              ))}
            </CTableBody>
          </CTable>
          {/* Reusable PaginationComponent */}
          <PaginationComponent
            apiEndpoint={`${BASE_URL}/api/mail-templates`}
            authToken={authToken}
            onDataFetched={setEmailTemplates}
            filters={filters}
          />
        </CCol>
      </CRow>
    </>
  )
}

export default EmailTemplates
