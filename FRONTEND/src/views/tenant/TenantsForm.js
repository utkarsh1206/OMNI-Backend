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

const TenantsForm = () => {
  const { id } = useParams() // Get tenant ID from URL if present
  const navigate = useNavigate()
  const isEditMode = Boolean(id)
  // const editAccess = boolean(id)
  const { authToken } = useContext(AuthContext)

  const [formData, setFormData] = useState({
    // Basic Details
    username: '',
    first_name: '',
    last_name: '',
    password: '',
    mobile: '',
    email: '',
    domain_name: '',
    company_code: '',
    company_name: '',
    // Server Details
    server_serialnumber: '',
    server_ip: '',
    server_mac: '',
    domain_url: '',
    // Miscellaneous
    title: '',
    footer_content: '',
    footer_url: '',
    tenant_logo: null,
  })

  useEffect(() => {
    if (isEditMode) {
      // Fetch tenant data to prefill the form
      axios
        .get(`${BASE_URL}/api/tenants/${id}`, {
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
          console.error('Error fetching tenant data:', error)
          alert('Failed to fetch tenant data.')
        })
    }
  }, [id, isEditMode])

  // const handleChange = (e) => {
  //   const { name, value } = e.target
  //   setFormData((prevData) => ({
  //     ...prevData,
  //     [name]: value,
  //   }))
  // }

  const handleChange = (e) => {
    const { name, value, files } = e.target
    if (files) {
      setFormData((prevData) => ({
        ...prevData,
        [name]: files[0],
      }))
    } else {
      setFormData((prevData) => ({
        ...prevData,
        [name]: value,
      }))
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()

    const form = new FormData()
    for (const [key, value] of Object.entries(formData)) {
      form.append(key, value)
    }

    try {
      let response
      if (isEditMode) {
        // Update existing tenant
        response = await axios.put(`${BASE_URL}/api/tenants/${id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      } else {
        // Create new tenant
        response = await axios.post(`${BASE_URL}/api/tenants`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
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
              : `\nUsername: ${response.data.data.username}\nCompany Name: ${response.data.data.company_name}`),
        )
        navigate('/tenants') // Redirect to tenants list after success
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
      username: '',
      first_name: '',
      last_name: '',
      password: '',
      mobile: '',
      email: '',
      domain_name: '',
      company_code: '',
      company_name: '',
      server_serialnumber: '',
      server_ip: '',
      server_mac: '',
      domain_url: '',
      title: '',
      footer_content: '',
      footer_url: '',
      tenant_logo: null,
    })
    // if (isEditMode) {
    navigate('/tenants') // Redirect to tenants list if editing
    // }
  }

  return (
    <CForm onSubmit={handleSubmit}>
      {/* Basic Details Section */}
      <CRow>
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>{isEditMode ? 'Edit Tenant' : 'Create Tenant'}</CCardHeader>
            <CCardBody>
              <CRow>
                <FormGroup
                  label="Username"
                  name="username"
                  type="text"
                  value={formData.username}
                  onChange={handleChange}
                  required={!isEditMode} // Assuming username is not editable
                  disabled={isEditMode} // Assuming username is not editable
                />
                <FormGroup
                  label="First Name"
                  name="first_name"
                  type="text"
                  value={formData.first_name}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Last Name"
                  name="last_name"
                  type="text"
                  value={formData.last_name}
                  onChange={handleChange}
                  required
                />
                {!isEditMode && ( // Show password field only in create mode
                  <FormGroup
                    label="Password"
                    name="password"
                    type="password"
                    value={formData.password}
                    onChange={handleChange}
                    required
                  />
                )}
                <FormGroup
                  label="Mobile"
                  name="mobile"
                  type="text"
                  value={formData.mobile}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Email"
                  name="email"
                  type="text"
                  value={formData.email}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Domain Name"
                  name="domain_name"
                  type="text"
                  value={formData.domain_name}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Company Code"
                  name="company_code"
                  type="text"
                  value={formData.company_code}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Company Name"
                  name="company_name"
                  type="text"
                  value={formData.company_name}
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
                  label="Server Serial Number"
                  name="server_serialnumber"
                  type="text"
                  value={formData.server_serialnumber}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Server IP"
                  name="server_ip"
                  type="text"
                  value={formData.server_ip}
                  onChange={handleChange}
                  required
                />
                <FormGroup
                  label="Server MAC"
                  name="server_mac"
                  type="text"
                  value={formData.server_mac}
                  onChange={handleChange}
                  required
                />
                {!isEditMode && (
                  <FormGroup
                    label="Domain URL"
                    name="domain_url"
                    type="text"
                    value={formData.domain_url}
                    onChange={handleChange}
                    required
                  />
                )}
              </CRow>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      {/* License Details Section */}
      <CRow className="mt-4">
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>Miscellaneous</CCardHeader>
            <CCardBody>
              {/* Add License Details FormGroups here */}
              <CRow>
                <FormGroup
                  label="Title"
                  name="title"
                  type="text"
                  value={formData.title || ''}
                  onChange={handleChange}
                  required={false}
                />
                <FormGroup
                  label="Footer Content"
                  name="footer_content"
                  type="text"
                  value={formData.footer_content || ''}
                  onChange={handleChange}
                  required={false}
                />
                <FormGroup
                  label="Footer URL"
                  name="footer_url"
                  type="url"
                  value={formData.footer_url || ''}
                  onChange={handleChange}
                  required={false}
                />
                <FormGroup
                  label="Logo"
                  name="logo"
                  type="file"
                  // value={formData.tenant_logo || ''}
                  onChange={handleChange}
                  required={false}
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

export default TenantsForm
