import React, { useState } from 'react'
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
import { BASE_URL } from '../../config'

// Define FormGroup outside the main component
const FormGroup = ({ label, name, value, onChange, required, type }) => (
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
    />
  </CCol>
)

const CreateTenant = () => {
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
    // License Details
  })

  // Debugging: Log formData whenever it changes
  //   React.useEffect(() => {
  //     console.log('Current formData:', formData);
  //   }, [formData]);

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData((prevData) => ({
      ...prevData,
      [name]: value,
    }))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    console.log('Form Data Submitted:', formData)
    try {
      const response = await axios.post(`${BASE_URL}/api/tenants`, formData, {
        headers: {
          'Content-Type': 'application/json',
          'Access-Control-Allow-Origin': '*',
        },
        // withCredentials: true // if you are using cookies
      })

      if (response.status == 200) {
        alert(response.data.message + "\nUsername: " + response.data.data.username + "\nCompany Name: " + response.data.data.company_name)
      }
      handleCancel();
    } catch (error) {
      console.error('Login failed:', error)
      throw error
    }

    // You can add further processing here
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
    })
    console.log('Form Cancelled')
  }

  return (
    <CForm onSubmit={handleSubmit}>
      {/* Basic Details Section */}
      <CRow>
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>Basic Details</CCardHeader>
            <CCardBody>
              <CRow>
                <FormGroup
                  label="Username"
                  name="username"
                  type="text"
                  value={formData.username}
                  onChange={handleChange}
                  required
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
                <FormGroup
                  label="Password"
                  name="password"
                  type="password"
                  value={formData.password}
                  onChange={handleChange}
                  required
                />
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
                  //   required={false}
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
                <FormGroup
                  label="Domain URL"
                  name="domain_url"
                  type="text"
                  value={formData.domain_url}
                  onChange={handleChange}
                  required
                />
              </CRow>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      {/* License Details Section */}
      <CRow className="mt-4">
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>License Details</CCardHeader>
            <CCardBody>
              <CRow></CRow>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      {/* Buttons */}
      <CRow className="mt-2 mb-4">
        <CCol xs="12" md="12" className="d-flex justify-content-end">
          <CButton type="submit" color="primary" className="me-2">
            Submit
          </CButton>
          <CButton type="button" color="secondary" onClick={handleCancel}>
            Cancel
          </CButton>
        </CCol>
      </CRow>
    </CForm>
  )
}

export default CreateTenant
