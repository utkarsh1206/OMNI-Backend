import React, { useContext, useEffect, useState } from 'react'
import {
  CCard,
  CCardBody,
  CCardHeader,
  CCardFooter,
  CCardTitle,
  CButton,
  CFormTextarea,
  CListGroup,
  CListGroupItem,
  CRow,
  CCol,
} from '@coreui/react'
import axios from 'axios'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'
import { formatDateWithTime } from '../../utils/DateUtils'

const Notes = ({ ticket }) => {
  const { authToken } = useContext(AuthContext)
  const [notes, setNotes] = useState([])
  const [newNote, setNewNote] = useState('')

  useEffect(() => {
    axios
      .get(`${BASE_URL}/api/note?supportticket_id=${ticket.id}`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authToken}`,
        },
      })
      .then((response) => {
        setNotes(response.data)
      })
      .catch((error) => {
        console.error('Error fetching Notes:', error)
        alert('Failed to fetch Notes')
      })
  }, [ticket])

  const handleSaveNote = async () => {
    if (!newNote.trim()) {
      alert('Please enter a note.')
      return
    }

    try {
      const response = await axios.post(
        `${BASE_URL}/api/note`,
        {
          supportticket_id: ticket.id,
          note: newNote,
        },
        {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        },
      )

      // Update the notes state with the new note
      setNotes([response.data.data, ...notes])
      setNewNote('') // Clear the textarea
    } catch (error) {
      console.error('Error saving note:', error)
      alert('Failed to save the note')
    }
  }

  return (
    <CCard>
      <CCardHeader>
        <CCardTitle>Notes</CCardTitle>
      </CCardHeader>
      <CCardBody>
        <CRow>
          <CCol>
            <CFormTextarea
              id="notes"
              name="notes"
              rows={3}
              placeholder="Enter Notes Here..."
              value={newNote}
              onChange={(e) => setNewNote(e.target.value)}
            />
          </CCol>
        </CRow>
        <CRow>
          <CCol className="mt-2 d-grid gap-2 d-md-flex justify-content-md-end">
            <CButton color="primary" onClick={handleSaveNote}>
              Save
            </CButton>
          </CCol>
        </CRow>
        <hr />
        <CListGroup>
          {notes.map((note) => (
            // <CListGroupItem as="a" href="#" active>
            <CListGroupItem>
              <div className="d-flex w-100 justify-content-between">
                <h5 className="mb-1">Note</h5>
                <small>{formatDateWithTime(note.created_at)}</small>
              </div>
              <p className="mb-1">{note.note}</p>
              <small>Donec id elit non mi porta.</small>
            </CListGroupItem>
          ))}
          {/* <CListGroupItem as="a" href="#">
            <div className="d-flex w-100 justify-content-between">
              <h5 className="mb-1">List group item heading</h5>
              <small className="text-body-secondary">3 days ago</small>
            </div>
            <p className="mb-1">
              Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius
              blandit.
            </p>
            <small className="text-body-secondary">Donec id elit non mi porta.</small>
          </CListGroupItem>
          <CListGroupItem as="a" href="#">
            <div className="d-flex w-100 justify-content-between">
              <h5 className="mb-1">List group item heading</h5>
              <small className="text-body-secondary">3 days ago</small>
            </div>
            <p className="mb-1">
              Donec id elit non mi porta gravida at eget metus. Maecenas sed diam eget risus varius
              blandit.
            </p>
            <small className="text-body-secondary">Donec id elit non mi porta.</small>
          </CListGroupItem> */}
        </CListGroup>
      </CCardBody>
      {/* <CCardFooter className="d-grid gap-2 d-md-flex justify-content-md-end"> */}

      {/* </CCardFooter> */}
    </CCard>
  )
}

export default Notes
