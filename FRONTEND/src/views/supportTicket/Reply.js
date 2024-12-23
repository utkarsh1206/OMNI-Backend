import React, { useContext, useState } from 'react'
import {
  COffcanvas,
  COffcanvasHeader,
  COffcanvasBody,
  CCloseButton,
  CCol,
  COffcanvasTitle,
  CRow,
  CFormLabel,
  CFormSelect,
  CFormTextarea,
  CButton,
  CFormInput,
} from '@coreui/react'
import { BASE_URL, PRIORITY_TO_COLOR, STATUS_TO_COLOR, TYPE_TO_COLOR } from '../../config'
import axios from 'axios'
import { AuthContext } from '../../context/AuthContext'

const FormGroup = ({ label, name, value, onChange, required, type, disabled }) => (
  <CCol md="4" className="mb-3">
    <CFormLabel htmlFor={name}>
      {label} {required && <span style={{ color: 'red' }}>*</span>}
    </CFormLabel>
    <CFormInput
      type={type}
      id={name}
      name={name}
      value={value}
      onChange={onChange}
      required={required}
      disabled={disabled}
    />
  </CCol>
)

const Reply = ({ onClose, ticket }) => {
  const { authToken } = useContext(AuthContext)

  const [formData, setFormData] = useState({
    // Basic Details
    user_id: '1',
    default_type: `${ticket.default_type}`,
    default_priority: `${ticket.default_priority}`,
    default_status: `${ticket.default_status}`,
    departments: '',
    to: `${ticket.to_email}`,
    body: '',
    attachment: '',
    supportticket_id: `${ticket.id}`,
  })

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData((prevData) => ({
      ...prevData,
      [name]: value,
    }))
  }

  const handleReply = async () => {
    try {
      const response = await axios.post(`${BASE_URL}/api/mail-details`, formData, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authToken}`,
        },
      })

      // Update the notes state with the new note
      // setNotes([response.data.data, ...notes])
      // setNewNote('') // Clear the textarea
      if (response.status === 200) {
        alert(response.data.message)
      }
      handleCancel()
      // navigate('/spprt_tckt')
      onClose()
    } catch (error) {
      console.error('Error sending Reply:', error)
      alert('Failed to Reply')
    }
  }

  const handleCancel = () => {
    // Reset form or handle cancellation as needed
    setFormData({
      user_id: '1',
      default_type: `${ticket.default_type}`,
      default_priority: `${ticket.default_priority}`,
      default_status: `${ticket.default_status}`,
      departments: '',
      to: `${ticket.to_email}`,
      body: '',
      attachment: '',
      supportticket_id: `${ticket.id}`,
    })
    // if (isEditMode) {
    // navigate('/spprt_tckt')
    onClose()
    // }
  }

  return (
    <COffcanvas
      visible={true}
      placement="end"
      onHide={onClose}
      backdrop="static"
      style={{ width: '60%' }}
      scroll={true}
    >
      <COffcanvasHeader>
        <CCol>
          <COffcanvasTitle>Reply to ticket</COffcanvasTitle>
        </CCol>
        <CCol xs="auto" className="ms-auto">
          <CCloseButton className="text-reset" onClick={onClose} />
        </CCol>
      </COffcanvasHeader>
      <hr />
      <COffcanvasBody>
        <CRow>
          <CCol md="4" className="mb-3">
            <CFormLabel htmlFor="default_type">
              Default Type <span style={{ color: 'red' }}>*</span>
            </CFormLabel>
            <CFormSelect
              id="default_type"
              name="default_type"
              value={formData.default_type}
              onChange={handleChange}
              required
            >
              <option key="" value="" disabled>
                Select Type
              </option>
              {Object.keys(TYPE_TO_COLOR).map((key) => (
                <option key={key} value={key}>
                  {key}
                </option>
              ))}
            </CFormSelect>
          </CCol>
          <CCol md="4" className="mb-3">
            <CFormLabel htmlFor="default_priority">
              Default Priority <span style={{ color: 'red' }}>*</span>
            </CFormLabel>
            <CFormSelect
              id="default_priority"
              name="default_priority"
              value={formData.default_priority}
              onChange={handleChange}
              required
            >
              <option key="" value="" disabled>
                Select Priority
              </option>
              {Object.keys(PRIORITY_TO_COLOR).map((key) => (
                <option key={key} value={key}>
                  {key}
                </option>
              ))}
            </CFormSelect>
          </CCol>
          <CCol md="4" className="mb-3">
            <CFormLabel htmlFor="default_status">
              Default Status <span style={{ color: 'red' }}>*</span>
            </CFormLabel>
            <CFormSelect
              id="default_status"
              name="default_status"
              value={formData.default_status}
              onChange={handleChange}
              required
            >
              <option key="" value="" disabled>
                Select Status
              </option>
              {Object.keys(STATUS_TO_COLOR).map((key) => (
                <option key={key} value={key}>
                  {key}
                </option>
              ))}
            </CFormSelect>
          </CCol>
          <CCol md="12" className="mb-3">
            <CFormLabel htmlFor="body">
              Reply <span style={{ color: 'red' }}>*</span>
            </CFormLabel>
            <CFormTextarea
              // type="textarea"
              id="body"
              name="body"
              rows={5}
              value={formData.body}
              onChange={handleChange}
              required
            />
          </CCol>
          <FormGroup
            label="Attachments"
            name="attachment"
            type="file"
            // value={formData.attachment}
            onChange={handleChange}
            required={false}
          />
        </CRow>
        {/* <CRow>
          <CCol className="d-flex justify-content-end">
            <CButton color="primary">Reply</CButton>
          </CCol>
          <CCol className="d-flex justify-content-end">
            <CButton color="warning">Cancel</CButton>
          </CCol>
        </CRow> */}
        <CRow className="justify-content-end">
          <CCol xs="auto">
            <CButton color="primary" onClick={handleReply}>
              Reply
            </CButton>
          </CCol>
          <CCol xs="auto">
            <CButton color="warning" onClick={handleCancel}>
              Cancel
            </CButton>
          </CCol>
        </CRow>
      </COffcanvasBody>
    </COffcanvas>
  )
}

export default Reply
