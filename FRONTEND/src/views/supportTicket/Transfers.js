// import React from 'react'
// import { CCard, CCardBody, CCardHeader, CCardTitle, CButton } from '@coreui/react'

// const Transfers = () => {
//   return (
//     <CCard>
//       <CCardHeader>
//         <CCardTitle>Transfers</CCardTitle>
//       </CCardHeader>
//       <CCardBody>
//         <div className="d-grid gap-2">
//           <CButton color="primary">Transfer Ticket</CButton>
//         </div>
//         <hr />
//         <h5>Transferred Ticket Logs</h5>
//         Transferred To: <strong>&lt;Name of new assignee&gt;</strong>
//         <br />
//         Transferred Time: <strong>&lt;Time&gt;</strong>
//         <br />
//       </CCardBody>
//     </CCard>
//   )
// }

// export default Transfers

// import React, { useState } from 'react'
// import {
//   CCard,
//   CCardBody,
//   CCardHeader,
//   CCardTitle,
//   CButton,
//   CModal,
//   CModalHeader,
//   CModalBody,
//   CModalFooter,
//   CFormSelect,
// } from '@coreui/react'

// const Transfers = ({ ticket }) => {
//   // State to manage modal visibility
//   const [visible, setVisible] = useState(false)

//   // State to handle dropdown selections
//   const [level, setLevel] = useState('')
//   const [userAgent, setUserAgent] = useState('')

//   return (
//     <>
//       <CCard>
//         <CCardHeader>
//           <CCardTitle>Transfers</CCardTitle>
//         </CCardHeader>
//         <CCardBody>
//           <div className="d-grid gap-2">
//             <CButton color="primary" onClick={() => setVisible(true)}>
//               Transfer Ticket
//             </CButton>
//           </div>
//           <hr />
//           <h5>Transferred Ticket Logs</h5>
//           Transferred To: <strong>&lt;Name of new assignee&gt;</strong>
//           <br />
//           Transferred Time: <strong>&lt;Time&gt;</strong>
//           <br />
//         </CCardBody>
//       </CCard>

//       {/* Modal for Transfer Ticket */}
//       <CModal visible={visible} onClose={() => setVisible(false)}>
//         <CModalHeader>Transfer Ticket</CModalHeader>
//         <CModalBody>
//           <div className="mb-3">
//             <label htmlFor="levelSelect" className="form-label">
//               Level
//             </label>
//             <CFormSelect
//               id="levelSelect"
//               value={level}
//               onChange={(e) => setLevel(e.target.value)}
//             >
//               <option value="">Select Level</option>
//               <option value="Level 1">Level 1</option>
//               <option value="Level 2">Level 2</option>
//               <option value="Level 3">Level 3</option>
//             </CFormSelect>
//           </div>
//           <div className="mb-3">
//             <label htmlFor="userAgentSelect" className="form-label">
//               User/Agent
//             </label>
//             <CFormSelect
//               id="userAgentSelect"
//               value={userAgent}
//               onChange={(e) => setUserAgent(e.target.value)}
//             >
//               <option value="">Select User/Agent</option>
//               <option value="Agent 1">Agent 1</option>
//               <option value="Agent 2">Agent 2</option>
//               <option value="Agent 3">Agent 3</option>
//             </CFormSelect>
//           </div>
//         </CModalBody>
//         <CModalFooter>
//           <CButton color="secondary" onClick={() => setVisible(false)}>
//             Close
//           </CButton>
//           <CButton
//             color="primary"
//             onClick={() => {
//               // Handle transfer logic here
//               console.log('Transferred to:', userAgent, 'at', level)
//               setVisible(false)
//             }}
//           >
//             Transfer
//           </CButton>
//         </CModalFooter>
//       </CModal>
//     </>
//   )
// }

// export default Transfers

import React, { useContext, useState } from 'react'
import {
  CCard,
  CCardBody,
  CCardHeader,
  CCardTitle,
  CButton,
  CModal,
  CModalHeader,
  CModalBody,
  CModalFooter,
  CFormSelect,
  CFormInput,
} from '@coreui/react'
import axios from 'axios'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'

const Transfers = ({ ticket }) => {
  const { authToken } = useContext(AuthContext)

  // State to manage modal visibility
  const [visible, setVisible] = useState(false)

  // State to handle dropdown selections and input
  const [level, setLevel] = useState('')
  const [userId, setUserId] = useState('')
  const [comment, setComment] = useState('')

  // Function to handle form submission
  const handleTransfer = async () => {
    try {
      console.log('st id: ' + `${ticket.support_ticket_id}`)
      const response = await axios.post(
        `${BASE_URL}/api/transfer`,
        {
          supportticket_id: `${ticket.support_ticket_id}`,
          user_id: userId,
          comment,
        },
        {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        },
      )
      console.log('Transfer Successful:', response.data)

      // Reset form fields and close modal
      setLevel('')
      setUserId('')
      setComment('')
      setVisible(false)
    } catch (error) {
      console.error('Error during transfer:', error)
    }
  }

  return (
    <>
      <CCard>
        <CCardHeader>
          <CCardTitle>Transfers</CCardTitle>
        </CCardHeader>
        <CCardBody>
          <div className="d-grid gap-2">
            <CButton color="primary" onClick={() => setVisible(true)}>
              Transfer Ticket
            </CButton>
          </div>
          <hr />
          <h5>Transferred Ticket Logs</h5>
          Transferred To: <strong>&lt;Name of new assignee&gt;</strong>
          <br />
          Transferred Time: <strong>&lt;Time&gt;</strong>
          <br />
        </CCardBody>
      </CCard>

      {/* Modal for Transfer Ticket */}
      <CModal visible={visible} onClose={() => setVisible(false)}>
        <CModalHeader>Transfer Ticket</CModalHeader>
        <CModalBody>
          {/* Level Dropdown */}
          <div className="mb-3">
            <label htmlFor="levelSelect" className="form-label">
              Level
            </label>
            <CFormSelect id="levelSelect" value={level} onChange={(e) => setLevel(e.target.value)}>
              <option value="">Select Level</option>
              <option value="Level 1">Level 1</option>
              <option value="Level 2">Level 2</option>
              <option value="Level 3">Level 3</option>
            </CFormSelect>
          </div>

          {/* User/Agent Dropdown */}
          <div className="mb-3">
            <label htmlFor="userAgentSelect" className="form-label">
              User/Agent
            </label>
            <CFormSelect
              id="userAgentSelect"
              value={userId}
              onChange={(e) => setUserId(e.target.value)}
            >
              <option value="">Select User/Agent</option>
              <option value="1">Agent 1</option>
              <option value="2">Agent 2</option>
              <option value="3">Agent 3</option>
            </CFormSelect>
          </div>

          {/* Comment Text Field */}
          <div className="mb-3">
            <label htmlFor="commentInput" className="form-label">
              Comment
            </label>
            <CFormInput
              id="commentInput"
              value={comment}
              onChange={(e) => setComment(e.target.value)}
              placeholder="Enter your comment"
            />
          </div>
        </CModalBody>
        <CModalFooter>
          <CButton color="secondary" onClick={() => setVisible(false)}>
            Close
          </CButton>
          <CButton
            color="primary"
            onClick={handleTransfer}
            disabled={!level || !userId || !comment} // Disable button if any field is empty
          >
            Transfer
          </CButton>
        </CModalFooter>
      </CModal>
    </>
  )
}

export default Transfers
