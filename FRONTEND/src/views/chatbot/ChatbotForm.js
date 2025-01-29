import React, { useState, useEffect, useContext } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CForm,
  CFormLabel,
  CFormTextarea,
  CRow,
  CCol,
  CFormSelect,
} from '@coreui/react'
import axios from 'axios'
import { useNavigate, useParams } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'

const FormGroup = ({ label, name, value, onChange, required, type, disabled, textarea }) => (
  <CCol md="6" className="mb-3">
    <CFormLabel htmlFor={name}>
      {label} {required && <span style={{ color: 'red' }}>*</span>}
    </CFormLabel>
    {textarea ? (
      <CFormTextarea
        id={name}
        name={name}
        value={value}
        onChange={onChange}
        rows="3"  // Adds a 3-line height for the textarea
        required={required}
        disabled={disabled}
      />
    ) : (
      <CFormInput
        type={type}
        id={name}
        name={name}
        value={value}
        onChange={onChange}
        required={required}
        disabled={disabled}
      />
    )}
  </CCol>
)

const ChatbotForm = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const isEditMode = Boolean(id)
  const { authToken } = useContext(AuthContext)

  const [formData, setFormData] = useState({
    question: '',
    answer: '',
    status: 'active',  // Set default status to 'active'
  })

  useEffect(() => {
    if (isEditMode) {
      axios
        .get(`${BASE_URL}/api/chatbots/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        })
        .then((response) => {
          const chatbotData = response.data.data
          setFormData({
            question: chatbotData.question,
            answer: chatbotData.answer,
            status: chatbotData.status ? 'active' : 'inactive',  // Set status based on chatbot data
          })
        })
        .catch((error) => {
          console.error('Error fetching Chatbot data:', error)
          alert('Failed to fetch Chatbot data.')
        })
    }
  }, [id, isEditMode, authToken])

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData((prevData) => ({
      ...prevData,
      [name]: value,
    }))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    try {
      let response
      if (isEditMode) {
        // Update existing chatbot
        response = await axios.put(`${BASE_URL}/api/chatbots/${id}`, formData, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        })
      } else {
        // Create new chatbot
        response = await axios.post(`${BASE_URL}/api/chatbots`, formData, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        })
      }

      if (response.status === 200) {
        alert(response.data.message + (isEditMode ? `\nChatbot updated successfully.` : `\nChatbot created successfully.`))
        navigate('/chatbots') // Redirect to Chatbot list page after success
      }
      handleCancel()
    } catch (error) {
      console.error('Operation failed:', error)
      alert('An error occurred while processing your request.')
    }
  }

  const handleCancel = () => {
    setFormData({
      question: '',
      answer: '',
      status: 'active',  // Reset status to default 'active'
    })
    navigate('/Chatbot') // Redirect to Chatbot list page if cancelling
  }

  return (
    <CForm onSubmit={handleSubmit}>
      {/* Basic Details Section */}
      <CRow>
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>{isEditMode ? 'Edit Chatbot' : 'Create Chatbot'}</CCardHeader>
            <CCardBody>
              <CRow>
                <FormGroup
                  label="Question"
                  name="question"
                  value={formData.question}
                  onChange={handleChange}
                  required={!isEditMode}
                  disabled={isEditMode}
                  textarea
                />
                <FormGroup
                  label="Answer"
                  name="answer"
                  value={formData.answer}
                  onChange={handleChange}
                  required
                  textarea
                />
                <CCol md="6" className="mb-3">
                  <CFormLabel htmlFor="status">
                    Status <span style={{ color: 'red' }}>*</span>
                  </CFormLabel>
                  <CFormSelect
                    id="status"
                    name="status"
                    value={formData.status}
                    onChange={handleChange}
                    required
                  >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </CFormSelect>
                </CCol>
              </CRow>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      {/* Buttons */}
      <CRow className="mt-2 mb-4">
        <CCol xs="12" md="12" className="d-flex justify-content-end">
          <CButton type="submit" color="primary" className="me-2">
            {isEditMode ? 'Update' : 'Submit'}
          </CButton>
          <CButton type="button" color="secondary" onClick={handleCancel}>
            Cancel
          </CButton>
        </CCol>
      </CRow>
    </CForm>
  )
}

export default ChatbotForm
