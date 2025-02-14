import React, { useContext, useState } from 'react'
import {
  CBadge,
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
import {
  BASE_URL,
  CAMPAIGNS,
  PRIORITY_TO_COLOR,
  PRODUCTS,
  STATUS_TO_COLOR,
  TYPE_TO_COLOR,
} from '../../config'
import { cilEnvelopeOpen, cilPencil, cilXCircle } from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../context/AuthContext'
import PaginationComponent from '../../components/PaginationComponent' // import the reusable pagination component
import { formatDate, formatDateWithTime } from '../../utils/DateUtils'
import axios from 'axios'
import SupportTicketModal from './SupportTicketModal'

const SupportTickets = () => {
  const navigate = useNavigate()
  const [tickets, setTickets] = useState([])
  const { authToken } = useContext(AuthContext)
  const [filters, setFilters] = useState({})

  const [ticketId, setTicketId] = useState()

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

  const [visible, setVisible] = useState(false)
  const handleTicketModal = (id) => {
    setTicketId(id)
    setVisible(true)
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
            <CRow>
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
                {/* <CTableHeaderCell scope="col">#</CTableHeaderCell> */}
                <CTableHeaderCell scope="col">Ticket No</CTableHeaderCell>
                <CTableHeaderCell scope="col">Request From</CTableHeaderCell>
                <CTableHeaderCell scope="col">Product</CTableHeaderCell>
                <CTableHeaderCell scope="col">Campaign</CTableHeaderCell>
                <CTableHeaderCell scope="col">Subject</CTableHeaderCell>
                <CTableHeaderCell scope="col">Ticket Type</CTableHeaderCell>
                <CTableHeaderCell scope="col">Ticket Priority</CTableHeaderCell>
                <CTableHeaderCell scope="col">Ticket Status</CTableHeaderCell>
                <CTableHeaderCell scope="col">Assigned User</CTableHeaderCell>
                {/* <CTableHeaderCell scope="col">Assigne Engg</CTableHeaderCell> */}
                <CTableHeaderCell scope="col">Actions</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {tickets.map((ticket) => (
                <CTableRow key={ticket.id}>
                  {/* <CTableDataCell className="align-content-center">{ticket.id}</CTableDataCell> */}
                  <CTableDataCell
                    className="align-content-center"
                    onClick={() => handleTicketModal(ticket.id)}
                  >
                    <Link to={`/spprt_tckt_view/${ticket.id}`}>{ticket.id}</Link>
                    {/* <CButton color="link" onClick={() => navigate(`/spprt_tckt_view`)}>
                      {ticket.id}
                    </CButton> */}
                  </CTableDataCell>
                  {/* Ticket No will be generated later */}
                  <CTableDataCell className="align-content-center">
                    {ticket.contact_id}
                  </CTableDataCell>
                  <CTableDataCell className="align-content-center">
                    {PRODUCTS.find((product) => String(product.id) === String(ticket.product_id))
                      ?.name || 'Product Not Found'}
                  </CTableDataCell>
                  <CTableDataCell className="align-content-center">
                    {CAMPAIGNS.find(
                      (campaign) => String(campaign.id) === String(ticket.campaign_id),
                    )?.name || 'Campaign Not Found'}
                  </CTableDataCell>
                  <CTableDataCell className="align-content-center">
                    <strong>{ticket.subject}</strong>
                    {''}
                    <br />
                    <small className="blockquote-footer">
                      {formatDateWithTime(ticket.created_at)}
                    </small>
                  </CTableDataCell>
                  <CTableDataCell className="align-content-center">
                    <CBadge color={TYPE_TO_COLOR[ticket.default_type] || 'secondary'}>
                      {ticket.default_type}
                    </CBadge>
                  </CTableDataCell>
                  <CTableDataCell className="align-content-center">
                    <CBadge color={PRIORITY_TO_COLOR[ticket.default_priority] || 'secondary'}>
                      {ticket.default_priority}
                    </CBadge>
                  </CTableDataCell>
                  <CTableDataCell className="align-content-center">
                    <CBadge color={STATUS_TO_COLOR[ticket.default_status] || 'secondary'}>
                      {ticket.default_status}
                    </CBadge>
                  </CTableDataCell>
                  <CTableDataCell className="align-content-center">
                    {ticket.assign_to_id}
                  </CTableDataCell>
                  {/* <CTableDataCell className="align-content-center">
                    {ticket.department}
                  </CTableDataCell> */}

                  <CTableDataCell className="align-content-center">
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
            filters={filters}
          />
        </CCol>
      </CRow>
      <SupportTicketModal
        visible={visible}
        setVisible={setVisible}
        id={ticketId}
        authToken={authToken}
      />
    </>
  )
}

export default SupportTickets
