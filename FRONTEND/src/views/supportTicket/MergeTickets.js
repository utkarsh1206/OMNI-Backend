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
  CFormSelect,
  CFormInput,
  CModalFooter,
} from '@coreui/react'
import { AuthContext } from '../../context/AuthContext'
import axios from 'axios'
import { BASE_URL } from '../../config'

const MergeTickets = ({ ticket }) => {
  const { authToken } = useContext(AuthContext)

  // State to manage modal visibility
  const [visible, setVisible] = useState(false)

  // State to handle dropdown selections and input
  const [level, setLevel] = useState('')
  const [mergeSupportTicket_id, setMergeSupportTicket_id] = useState('')
  const [comment, setComment] = useState('')

  // Function to handle form submission
  const handleMerge = async () => {
    try {
      const response = await axios.post(
        `${BASE_URL}/api/merge`,
        {
          supportticket_id: `${ticket.id}`,
          merge_supportticket_id: mergeSupportTicket_id,
        },
        {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        },
      )
      console.log('Merge Successful:', response.data)

      // Reset form fields and close modal
      setLevel('')
      setMergeSupportTicket_id('')
      setComment('')
      setVisible(false)
    } catch (error) {
      console.error('Error during merge:', error)
    }
  }

  return (
    <>
      <CCard>
        <CCardHeader>
          <CCardTitle>Merge Tickets</CCardTitle>
        </CCardHeader>
        <CCardBody>
          <div className="d-grid gap-2">
            <CButton color="primary" onClick={() => setVisible(true)}>
              Merge Ticket
            </CButton>
          </div>
          <hr />
          <h5>Merged Ticket Logs</h5>
          Merged with: <strong>&lt;Ticket Number&gt;</strong>
          <br />
          Merged Time: <strong>&lt;Time&gt;</strong>
          <br />
          Merge Note: <strong>&lt;Note&gt;</strong>
          <br />
        </CCardBody>
      </CCard>

      {/* Modal for Merge Ticket */}
      <CModal visible={visible} onClose={() => setVisible(false)}>
        <CModalHeader>Merge Ticket</CModalHeader>
        <CModalBody>
          {/* Level Dropdown */}
          {/* <div className="mb-3">
            <label htmlFor="levelSelect" className="form-label">
              Level
            </label>
            <CFormSelect id="levelSelect" value={level} onChange={(e) => setLevel(e.target.value)}>
              <option value="">Select Level</option>
              <option value="Level 1">Level 1</option>
              <option value="Level 2">Level 2</option>
              <option value="Level 3">Level 3</option>
            </CFormSelect>
          </div> */}

          {/* Merge Ticket Number field */}
          <div className="mb-3">
            <label htmlFor="mergeSupportTicket_id" className="form-label">
              Support Ticket Number to be Merged
            </label>
            <CFormInput
              id="mergeSupportTicket_id"
              value={mergeSupportTicket_id}
              onChange={(e) => setMergeSupportTicket_id(e.target.value)}
              placeholder="Enter your comment"
            />
          </div>

          {/* Comment Text Field */}
          {/* <div className="mb-3">
            <label htmlFor="commentInput" className="form-label">
              Comment
            </label>
            <CFormInput
              id="commentInput"
              value={comment}
              onChange={(e) => setComment(e.target.value)}
              placeholder="Enter your comment"
            />
          </div> */}
        </CModalBody>
        <CModalFooter>
          <CButton color="secondary" onClick={() => setVisible(false)}>
            Close
          </CButton>
          <CButton
            color="primary"
            onClick={handleMerge}
            // disabled={!level || !mergeSupportTicket_id || !comment} // Disable button if any field is empty
            disabled={!mergeSupportTicket_id } // Disable button if any field is empty
          >
            Merge
          </CButton>
        </CModalFooter>
      </CModal>
    </>
  )
}

export default MergeTickets
