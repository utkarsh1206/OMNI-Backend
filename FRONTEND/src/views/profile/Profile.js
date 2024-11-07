import React, { useContext, useState } from 'react'
import {
  CButton,
  CButtonGroup,
  CCard,
  CCardBody,
  CCardFooter,
  CCardHeader,
  CCardImage,
  CCardText,
  CCardTitle,
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
import { cilEnvelopeOpen, cilPencil, cilUser, cilXCircle } from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../context/AuthContext'
import PaginationComponent from '../../components/PaginationComponent' // import the reusable pagination component
import { formatDate } from '../../utils/DateUtils'
import axios from 'axios'

const ViewSupportTickets = () => {
  const navigate = useNavigate()
  const [tickets, setTickets] = useState([])
  const { authToken } = useContext(AuthContext)
//   const profile_img = "src/assets/brand/new_menu_logo.png"
  const profile_img = ""
  const email = "test@test.com"
  const name = "test name"

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
            {/* <CCard style={{ width: '18rem' }}>
              <CCardImage orientation="top" src="src\assets\brand\new_menu_logo.png" />
              <CCardBody>
                <CCardText>
                  Some quick example text to build on the card title and make up the bulk of the
                  card's content.
                </CCardText>
              </CCardBody>
            </CCard> */}

            <CCard
              style={{
                width: '18rem',
                textAlign: 'center',
                borderRadius: '8px',
                overflow: 'hidden',
              }}
            >
              <CCardBody
                style={{
                  backgroundColor: profile_img ? '#f0f0f0' : '#0d6efd', // Background color for image and fallback
                  color: '#fff',
                  padding: '2rem',
                  display: 'flex',
                  flexDirection: 'column',
                  alignItems: 'center',
                  justifyContent: 'center',
                  height: '250px', // Set height to make room for the background image
                  backgroundImage: profile_img ? `url(${profile_img})` : 'none',
                  backgroundSize: 'contain',
                  backgroundPosition: 'center',
                  backgroundRepeat: 'no-repeat',
                }}
              >
                {/* Conditionally render the icon only if no profile image */}
                {!profile_img && (
                  <div
                    style={{
                      width: '100px',
                      height: '100px',
                      backgroundColor: '#e0e0e0',
                      borderRadius: '50%',
                      display: 'flex',
                      alignItems: 'center',
                      justifyContent: 'center',
                      fontSize: '24px',
                      color: '#555',
                      marginBottom: '1rem',
                    }}
                  >
                    <CIcon icon={cilUser} size="xl" />
                  </div>
                )}
                </CCardBody>
                <CCardBody>
                <CCardTitle>{name}</CCardTitle>
                <CCardText>{email}</CCardText>
              </CCardBody>
              <CCardFooter style={{ padding: '1rem', display: 'flex', justifyContent: 'center' }}>
                <CButton color="link" style={{ color: '#dc3545', textDecoration: 'none' }}>
                  <CIcon icon={cilUser} className="me-2" />
                  Profile
                </CButton>
              </CCardFooter>
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
