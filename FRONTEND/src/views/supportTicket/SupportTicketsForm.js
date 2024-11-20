import React, { useState, useEffect, useContext } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CForm,
  CFormLabel,
  CFormInput,
  CRow,
  CCol,
} from '@coreui/react'
import axios from 'axios'
import { useNavigate, useParams } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'
import DefaultCampaignComponent from '../campaign/DefaultCampaignComponent'
import DefaultProductsComponent from '../product/DefaultProductsComponent'

// Define FormGroup outside the main component
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

const SupportTicketsForm = () => {
  const { id } = useParams() // Get tenant ID from URL if present
  const navigate = useNavigate()
  const isEditMode = Boolean(id)
  // const editAccess = boolean(id)
  const { authToken } = useContext(AuthContext)

  const [formData, setFormData] = useState({
    // Basic Details
    product_id: '',
    campaign_id: '',
    inbound_group_id: '',
    contact_id: '',
    assign_to_id: '',
    default_type: '',
    default_priority: '',
    default_status: '',
    subject: '',
    // Server Details
    departments: '',
    to_emails: '',
    description: '',
    attachment: '',
    status: '',
    // License Details
  })

  useEffect(() => {
    if (isEditMode) {
      // Fetch tenant data to prefill the form
      axios
        .get(`${BASE_URL}/api/supportticket/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        .then((response) => {
          setFormData(response.data.data)
        })
        .catch((error) => {
          console.error('Error fetching Support Ticket data:', error)
          alert('Failed to fetch Support Ticket data.')
        })
    }
  }, [id, isEditMode])

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
        // Update existing tenant
        response = await axios.put(`${BASE_URL}/api/supportticket/${id}`, formData, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      } else {
        // Create new tenant
        response = await axios.post(`${BASE_URL}/api/supportticket`, formData, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      }

      // if (response.status === 200 || response.status === 201) {
      if (response.status === 200) {
        alert(
          response.data.message +
            (isEditMode
              ? // ? `\nTenant updated successfully.`
                ``
              : `\n ID: ${response.data.data.id}\n Subject: ${response.data.data.subject}`),
        )
        navigate('/spprt_tckt') // Redirect to tenants list after success
      }
      handleCancel()
    } catch (error) {
      console.error('Operation failed:', error)
      alert('An error occurred while processing your request.')
    }
  }

  const handleCancel = () => {
    // Reset form or handle cancellation as needed
    setFormData({
      product_id: '',
      campaign_id: '',
      inbound_group_id: '',
      contact_id: '',
      assign_to_id: '',
      default_type: '',
      default_priority: '',
      default_status: '',
      subject: '',
      departments: '',
      to_emails: '',
      description: '',
      attachment: '',
      status: '',
    })
    // if (isEditMode) {
    navigate('/spprt_tckt') // Redirect to tenants list if editing
    // }
  }

  return (
    <CForm onSubmit={handleSubmit}>
      {/* Basic Details Section */}
      <CRow>
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>{isEditMode ? 'Edit Ticket' : 'Create Ticket'}</CCardHeader>
            <CCardBody>
              <CRow>
                <DefaultProductsComponent formData={formData} handleChange={handleChange} />
                <DefaultCampaignComponent formData={formData} handleChange={handleChange} />
                <FormGroup
                  label="Inbound Group"
                  name="inbound_group_id"
                  type="text"
                  value={formData.inbound_group_id}
                  onChange={handleChange}
                  required
                />
              
                <FormGroup
                  label="Contact"
                  name="contact_id"
                  type="text"
                  value={formData.contact_id}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Assign To"
                  name="assign_to_id"
                  type="text"
                  value={formData.assign_to_id}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Default Type"
                  name="default_type"
                  type="text"
                  value={formData.default_type}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Default Priority"
                  name="default_priority"
                  type="text"
                  value={formData.default_priority}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Default Status"
                  name="default_status"
                  type="text"
                  value={formData.default_status}
                  onChange={handleChange}
                  required
                />
              </CRow>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      {/* Server Details Section */}
      <CRow className="mt-4">
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>Server Details</CCardHeader>
            <CCardBody>
              <CRow>
                <FormGroup
                  label="Subject"
                  name="subject"
                  type="text"
                  value={formData.subject}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Departments"
                  name="departments"
                  type="text"
                  value={formData.departments}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="To Mail(s)"
                  name="to_emails"
                  type="text"
                  value={formData.to_emails}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Description"
                  name="description"
                  type="text"
                  value={formData.description}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Attachments"
                  name="attachment"
                  type="text"
                  value={formData.attachment}
                  onChange={handleChange}
                  required
                />
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

export default SupportTicketsForm
