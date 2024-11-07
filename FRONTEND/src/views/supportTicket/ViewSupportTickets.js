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

const ViewSupportTickets = () => {
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
      <CContainer className="px-4">
        <CRow xs={{ gutterX: 5 }}>
          <CCol md={7} className="p-2">
            <CCard>
              <CCardHeader>
                Support Ticket Details - <small>UserName</small>
              </CCardHeader>
              <CCardBody>
                <CRow className="pb-2">
                  <CCol sm={3}>Image</CCol>
                  <CCol>Details</CCol>
                </CRow>
                <CRow>
                  <CCol>
                    <strong>Description</strong>
                  </CCol>
                </CRow>
              </CCardBody>
            </CCard>
          </CCol>
          <CCol className="p-2">
            <CCard>
              <CCardHeader></CCardHeader>
              <CCardBody></CCardBody>
            </CCard>
          </CCol>
        </CRow>
      </CContainer>
    </>
  )
}

export default ViewSupportTickets
