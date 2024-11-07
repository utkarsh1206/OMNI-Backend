import React, { useContext, useState } from 'react'
import {
  CButton,
  CButtonGroup,
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
import { cilEnvelopeOpen, cilPencil, cilXCircle } from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../context/AuthContext'
import PaginationComponent from '../../components/PaginationComponent' // import the reusable pagination component
import { formatDate } from '../../utils/DateUtils'
import axios from 'axios'

const SupportTickets = () => {
  const navigate = useNavigate()
  const [tickets, setTickets] = useState([])
  const { authToken } = useContext(AuthContext)

  const handleDeleteTicket = async (id, username) => {
    if (window.confirm('Are you sure to delete ' + username + '?')) {
      try {
        let response = await axios.delete(`${BASE_URL}/api/supportticket/${id}`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          // Optionally, refetch the data
          setTickets((prev) => prev.filter((tenant) => tenant.id !== id)) // Immediately update the UI
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
              Search for <strong>Tickets</strong> <small>Filters will be filled later</small>
            </CCardHeader>
            <CCardBody>
              Placeholder for ticket search fields
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
                <Link to="/spprt_tckt/crt_tckt">
                  <CButton color="primary" className="mb-4 me-2" size="sm">
                    Create Ticket
                  </CButton>
                </Link>
              </CCol>
            </CRow>
          </CContainer>
          <CTable hover small bordered responsive="md">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell>
                <CTableHeaderCell scope="col">Ticket No</CTableHeaderCell>
                <CTableHeaderCell scope="col">Request From</CTableHeaderCell>
                <CTableHeaderCell scope="col">Product</CTableHeaderCell>
                <CTableHeaderCell scope="col">Campaign</CTableHeaderCell>
                <CTableHeaderCell scope="col">Subject</CTableHeaderCell>
                <CTableHeaderCell scope="col">Ticket Type</CTableHeaderCell>
                <CTableHeaderCell scope="col">Ticket Priority</CTableHeaderCell>
                <CTableHeaderCell scope="col">Ticket Status</CTableHeaderCell>
                <CTableHeaderCell scope="col">Assigned User</CTableHeaderCell>
                <CTableHeaderCell scope="col">Assigne Engg</CTableHeaderCell>
                <CTableHeaderCell scope="col">Actions</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {console.log(tickets)}
              {tickets.map((ticket) => (
                <CTableRow key={ticket.id}>
                  <CTableDataCell>{ticket.id}</CTableDataCell>
                  <CTableDataCell>
                    <CButton color="link" onClick={() => navigate(`/spprt_tckt_view`)}>
                      {ticket.id}
                    </CButton>
                  </CTableDataCell>
                  {/* Ticket No will be generated later */}
                  <CTableDataCell>{ticket.contact_id}</CTableDataCell>
                  {/* Requesr From */}
                  <CTableDataCell>{ticket.product_id}</CTableDataCell>
                  <CTableDataCell>{ticket.campaign_id}</CTableDataCell>
                  <CTableDataCell>
                    <strong>{ticket.subject}</strong>{' '}
                    <small className="blockquote-footer">{formatDate(ticket.created_at)}</small>
                  </CTableDataCell>
                  <CTableDataCell>{ticket.default_type}</CTableDataCell>
                  <CTableDataCell>{ticket.default_priority}</CTableDataCell>
                  <CTableDataCell>{ticket.default_status}</CTableDataCell>
                  <CTableDataCell>{ticket.assign_to_id}</CTableDataCell>
                  <CTableDataCell>{ticket.department}</CTableDataCell>

                  <CTableDataCell>
                    <div style={{ display: 'flex', justifyContent: 'center', gap: '8px' }}>
                      <CTooltip content="Edit">
                        <CButton
                          color="warning"
                          size="sm"
                          onClick={() => navigate(`/spprt_tckt/edit/${ticket.id}`)}
                        >
                          <CIcon icon={cilPencil} />
                        </CButton>
                      </CTooltip>
                      <CTooltip content="Mail">
                        <CButton
                          color="secondary"
                          size="sm"
                          // onClick={() => handleMailTicket(ticket.id)}
                        >
                          <CIcon icon={cilEnvelopeOpen} />
                        </CButton>
                      </CTooltip>
                      <CTooltip content="Delete">
                        <CButton
                          color="danger"
                          size="sm"
                          onClick={() => handleDeleteTicket(ticket.id, ticket.username)}
                        >
                          <CIcon icon={cilXCircle} />
                        </CButton>
                      </CTooltip>
                    </div>
                  </CTableDataCell>
                </CTableRow>
              ))}
            </CTableBody>
          </CTable>

          {/* Reusable PaginationComponent */}
          <PaginationComponent
            apiEndpoint={`${BASE_URL}/api/supportticket`}
            authToken={authToken}
            onDataFetched={setTickets}
            // params={[testing]}
          />
        </CCol>
      </CRow>
    </>
  )
}

export default SupportTickets
