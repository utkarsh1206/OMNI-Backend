// import React, { useContext, useEffect, useState } from 'react'
// import {
//   CBadge,
//   CCard,
//   CCardBody,
//   CCardHeader,
//   CCardTitle,
//   CCol,
//   CListGroup,
//   CListGroupItem,
//   CRow,
//   CSpinner,
// } from '@coreui/react'
// import { Link } from 'react-router-dom'
// import { cilBuilding, cilMap, cilMobile } from '@coreui/icons'
// import CIcon from '@coreui/icons-react'
// import { formatDateWithTime } from '../../utils/DateUtils'
// import { BASE_URL, PRIORITY_TO_COLOR, STATUS_TO_COLOR, TYPE_TO_COLOR } from '../../config'
// import { AuthContext } from '../../context/AuthContext'
// import axios from 'axios'

// const TicketDescription = ({ ticket }) => {
//   if (!ticket || !ticket.id)
//     return (
//       <CCard className="mb-3">
//         <CCardBody>
//           <div className="text-center">
//             <CSpinner color="primary" />
//             <p>Loading ticket data...</p>
//           </div>
//         </CCardBody>
//       </CCard>
//     ) // Avoid rendering until data is loaded

//   const { authToken } = useContext(AuthContext)
//   const [mailDetails, setMailDetails] = useState([])

//   useEffect(() => {
//     axios
//       .get(`${BASE_URL}/api/mail-details?supportticket_id=${ticket.id}`, {
//         headers: {
//           'Content-Type': 'application/json',
//           Authorization: `Bearer ${authToken}`,
//         },
//       })
//       .then((response) => {
//         setMailDetails(response.data)
//       })
//       .catch((error) => {
//         console.error('Error fetching Notes:', error)
//         alert('Failed to fetch Notes')
//       })
//   }, [ticket])

//   return (
//     <CCard>
//       <CCardHeader>
//         <CCardTitle>Ticket Description</CCardTitle>
//       </CCardHeader>
//       <CCardBody>
//         <CListGroup>
//           {mailDetails.map((mail, index) => (
//             // <CListGroupItem as="a" href="#" active>
//             <CListGroupItem>
//               <div className="d-flex w-100 justify-content-between">
//                 <h5 className="mb-1">Mail from {mail.to}</h5>
//                 <small>{formatDateWithTime(mail.date)}</small>
//               </div>
//               <p className="mb-1">{mail.body}</p>
//               <small>
//                 <CRow>
//                   <CCol>Type: {mail.type}</CCol>
//                   <CCol>Priority: {mail.priority}</CCol>
//                   <CCol>Status: {mail.status}</CCol>
//                 </CRow>
//               </small>
//             </CListGroupItem>
//           ))}
//         </CListGroup>
//       </CCardBody>
//     </CCard>
//   )
// }

// export default TicketDescription

import React, { useContext, useEffect, useState } from 'react'
import {
  CBadge,
  CCard,
  CCardBody,
  CCardHeader,
  CCardTitle,
  CCol,
  CListGroup,
  CListGroupItem,
  CRow,
  CSpinner,
} from '@coreui/react'
import { formatDateWithTime } from '../../utils/DateUtils'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'
import axios from 'axios'

const TicketDescription = ({ ticket }) => {
  if (!ticket || !ticket.id)
    return (
      <CCard className="mb-3">
        <CCardBody>
          <div className="text-center">
            <CSpinner color="primary" />
            <p>Loading ticket data...</p>
          </div>
        </CCardBody>
      </CCard>
    ) // Avoid rendering until data is loaded

  const { authToken } = useContext(AuthContext)
  const [mailDetails, setMailDetails] = useState([])

  useEffect(() => {
    axios
      .get(`${BASE_URL}/api/mail-details?supportticket_id=${ticket.support_ticket_id}`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authToken}`,
        },
      })
      .then((response) => {
        setMailDetails(
          response.data.map((mail) => ({
            ...mail,
            isExpanded: false, // Add a property to control expansion
          })),
        )
      })
      .catch((error) => {
        console.error('Error fetching Ticket Description:', error)
        alert('Failed to Ticket Description')
      })
  }, [ticket])

  const toggleExpand = (index) => {
    setMailDetails((prevDetails) =>
      prevDetails.map((mail, i) =>
        i === index ? { ...mail, isExpanded: !mail.isExpanded } : mail,
      ),
    )
  }

  const truncateText = (text, isExpanded) => {
    const maxLength = 200 // Maximum characters before truncation
    if (isExpanded || text.length <= maxLength) return text
    return `${text.slice(0, maxLength)}...`
  }

  return (
    <CCard className="mb-3">
      <CCardHeader>
        <CCardTitle>Ticket Description</CCardTitle>
      </CCardHeader>
      <CCardBody>
        <CListGroup>
          {mailDetails.map((mail, index) => (
            <CListGroupItem key={index}>
              <div className="d-flex w-100 justify-content-between">
                <h5 className="mb-1">Mail from {mail.to}</h5>
                <small>{formatDateWithTime(mail.date)}</small>
              </div>
              <p className="mb-1">{truncateText(mail.body, mail.isExpanded)}</p>
              {!mail.isExpanded && mail.body.length > 100 && (
                <div
                  style={{
                    background: 'linear-gradient(to bottom, transparent, white)',
                    position: 'relative',
                    height: '40px',
                    marginTop: '-40px',
                  }}
                ></div>
              )}
              {mail.body.length > 100 && (
                <button onClick={() => toggleExpand(index)} className="btn btn-link p-0 mb-2">
                  {mail.isExpanded ? 'View Less' : 'View More'}
                </button>
              )}
              <small>
                <CRow>
                  <CCol>Type: {mail.type}</CCol>
                  <CCol>Priority: {mail.priority}</CCol>
                  <CCol>Status: {mail.status}</CCol>
                </CRow>
              </small>
            </CListGroupItem>
          ))}
        </CListGroup>
      </CCardBody>
    </CCard>
  )
}

export default TicketDescription
