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
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { BASE_URL } from '../../../config'
import { cilPencil, cilXCircle } from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../../context/AuthContext'
import PaginationComponent from '../../../components/PaginationComponent' // import the reusable pagination component
import { formatDate } from '../../../utils/DateUtils'
import axios from 'axios'

const EmailConfigSettings = () => {
  const navigate = useNavigate()
  const location = useLocation()
  const [emailConfigs, setEmailConfigs] = useState([])
  const { authToken } = useContext(AuthContext)

  const [filters, setFilters] = useState({
    category: '',
    status: '',

    // Add more filters here as needed
  })

  // Extract type from query string
  const searchParams = new URLSearchParams(location.search);
  const emailType = searchParams.get("type");

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
        let response = await axios.delete(`${BASE_URL}/api/email-configure/${id}`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          // Optionally, refetch the data
          setEmailConfigs((prev) => prev.filter((tenant) => tenant.id !== id)) // Immediately update the UI
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
              Search for <strong>Email Configuration</strong> <small>Filters will be filled later</small>
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
      {/* <h5>Email Configurations for: {emailType?.toUpperCase()}</h5> */}
      <CRow>
        <CCol>
          <CContainer>
            <CRow fluid responsive>
              <CCol md={8}>
                <Link to={`/settings/email_config_settings/crt_config?type=${emailType}`}>
                  <CButton color="primary" className="mb-4 me-2" size="sm">
                    Configure
                  </CButton>
                </Link>
              </CCol>
            </CRow>
          </CContainer>
          <CTable hover bordered responsive="md">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell>
                <CTableHeaderCell scope="col">Product</CTableHeaderCell>
                <CTableHeaderCell scope="col">Campaign</CTableHeaderCell>
                <CTableHeaderCell scope="col">Inbound Group</CTableHeaderCell>
                <CTableHeaderCell scope="col">SMTP User</CTableHeaderCell>
                <CTableHeaderCell scope="col">IMAP User</CTableHeaderCell>
                <CTableHeaderCell scope="col">Controls</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {emailConfigs.map((emailConfig) => (
                <CTableRow key={emailConfig.id}>
                  <CTableDataCell>{emailConfig.id}</CTableDataCell>
                  <CTableDataCell>{emailConfig.product_id}</CTableDataCell>
                  <CTableDataCell>{emailConfig.campaign_id}</CTableDataCell>
                  <CTableDataCell>{emailConfig.inbound_id}</CTableDataCell>
                  <CTableDataCell>{emailConfig.smtp_user}</CTableDataCell>
                  <CTableDataCell>{emailConfig.imap_user}</CTableDataCell>
                  {/* <CTableDataCell>{formatDate(emailConfig.created_at)}</CTableDataCell> */}
                  <CTableDataCell>
                    <CRow className="justify-content-center mx-0">
                      <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="Edit">
                          <CButton
                            color="warning"
                            size="sm"
                            onClick={() => navigate(`/settings/email_config_settings/edit/${emailConfig.id}`)}
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
                            onClick={() => handleDeleteConfig(emailConfig.id, '')}
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
            apiEndpoint={`${BASE_URL}/api/email-configure`}
            authToken={authToken}
            onDataFetched={setEmailConfigs}
            filters={filters}
          />
        </CCol>
      </CRow>
    </>
  )
}

export default EmailConfigSettings
