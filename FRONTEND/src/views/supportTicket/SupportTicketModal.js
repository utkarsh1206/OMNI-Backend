import React, { useEffect, useState } from 'react'
import {
  CBadge,
  CButton,
  CCol,
  CModal,
  CModalBody,
  CModalFooter,
  CModalHeader,
  CModalTitle,
  CRow,
  CSpinner,
} from '@coreui/react'
import { BASE_URL, PRIORITY_TO_COLOR, STATUS_TO_COLOR, TYPE_TO_COLOR } from '../../config'
import axios from 'axios'
import { Link } from 'react-router-dom'
import { formatDateWithTime } from '../../utils/DateUtils'
import CIcon from '@coreui/icons-react'
import { cilBuilding, cilMap, cilMobile } from '@coreui/icons'


const SupportTicketModal = ({ visible, setVisible, id, authToken }) => {
  const [tickets, setTickets] = useState(null) // State for ticket data
  const [loading, setLoading] = useState(false) // State for loading spinner

  useEffect(() => {
    // Only fetch data if the modal is visible and there's a valid ticket ID
    if (visible && id) {
      setLoading(true) // Start the loader
      axios
        .get(`${BASE_URL}/api/supportticket/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        .then((response) => {
          setTickets(response.data.data) // Set the ticket data
        })
        .catch((error) => {
          console.error('Error fetching Support Ticket data:', error)
          alert('Failed to fetch Support Ticket data.')
        })
        .finally(() => {
          setLoading(false) // Stop the loader
        })
    }
  }, [visible, id, authToken])



  // Render loader while fetching data
  if (loading) {
    return (
      <CModal
        size="lg"
        backdrop="static"
        visible={visible}
        onClose={() => setVisible(false)}
        aria-labelledby="LiveDemoExampleLabel"
      >
        <CModalBody>
          <div className="text-center">
            <CSpinner color="primary" />
            <p>Loading ticket data...</p>
          </div>
        </CModalBody>
      </CModal>
    )
  }

  // Render modal content when data is available
  return (
    <CModal
      size="lg"
      backdrop="static"
      visible={visible}
      onClose={() => setVisible(false)}
      aria-labelledby="LiveDemoExampleLabel"
      className="responsive-modal"
    >
      {tickets ? (
        <>
          <CModalHeader>
            <CModalTitle>
              Ticket Details - <small>{tickets.assign_to_id} (Assigned to username)</small> -{' '}
              <CBadge color={tickets.default_status == 'Unassigned' ? 'warning' : 'success'}>
                {tickets.default_status == 'Unassigned' ? 'Unassigned' : 'Assigned'}
              </CBadge>
            </CModalTitle>
          </CModalHeader>
          <CModalBody>
            <CRow>
              <CCol sm={2} md={3} className="align-content-center text-center">
                Image
              </CCol>
              <CCol>
                <h6>
                  Ticket Number : #{tickets.id} - {formatDateWithTime(tickets.created_at)}
                </h6>
                <h5>
                  <Link to={`#`}>{tickets.contact_id}</Link>
                </h5>
                <p>
                  <CIcon icon={cilBuilding} />{' '}
                  <Link to={`#`}>
                    <strong>{tickets.account_id}</strong>
                  </Link>
                </p>
                <p>
                  <CIcon icon={cilMobile} /> (Contact's mobile number)
                </p>
                <p>
                  <CIcon icon={cilMap} /> (Contact's Address)
                </p>
                <strong>Subject : </strong> {tickets.subject}
                <CRow className="my-3">
                  <CCol sm={4}>
                    <strong>Type: </strong>{' '}
                    <CBadge color={TYPE_TO_COLOR[tickets.default_type] || 'secondary'}>
                      {tickets.default_type}
                    </CBadge>
                  </CCol>
                  <CCol sm={4}>
                    <strong>Priority :</strong>{' '}
                    <CBadge color={PRIORITY_TO_COLOR[tickets.default_priority] || 'secondary'}>
                      {tickets.default_priority}
                    </CBadge>
                  </CCol>
                  <CCol md={4}>
                    <strong>Status :</strong>{' '}
                    <CBadge color={STATUS_TO_COLOR[tickets.default_status] || 'secondary'}>
                      {tickets.default_status}
                    </CBadge>
                  </CCol>
                </CRow>
              </CCol>
            </CRow>
            <CRow className="mb-4">
              <h6>Attachments:</h6>
            </CRow>
          </CModalBody>
          <CModalFooter>
            <CButton color="secondary" onClick={() => setVisible(false)}>
              Close
            </CButton>
          </CModalFooter>
        </>
      ) : (
        <CModalBody>
          <p>No data available for the selected ticket.</p>
        </CModalBody>
      )}
    </CModal>
  )
}

export default SupportTicketModal
