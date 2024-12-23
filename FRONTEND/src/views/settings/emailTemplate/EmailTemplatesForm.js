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
  CCardTitle,
  CCardText,
  CCardFooter,
  CFormTextarea,
  CFormSelect,
} from '@coreui/react'
import axios from 'axios'
import { useLocation, useNavigate, useParams } from 'react-router-dom'
import { BASE_URL, STATUS_TO_COLOR, TYPE_TO_COLOR } from '../../../config'
import { AuthContext } from '../../../context/AuthContext'
import CIcon from '@coreui/icons-react'
import { cilPencil, cilUser } from '@coreui/icons'
import DefaultCampaignComponent from '../../campaign/DefaultCampaignComponent'
import DefaultProductsComponent from '../../product/DefaultProductsComponent'

// Define FormGroup outside the main component
// const FormGroup = ({ label, name, value, onChange, required, type, disabled }) => (
//   <CCol md="4" className="mb-3">
//     <CFormLabel htmlFor={name}>
//       {label} {required && <span style={{ color: 'red' }}>*</span>}
//     </CFormLabel>
//     <CFormInput
//       type={type}
//       id={name}
//       name={name}
//       value={value}
//       onChange={onChange}
//       required={required}
//       disabled={disabled}
//     />
//   </CCol>
// )

const EmailTemplatesForm = () => {
  const { id } = useParams() // Get Template ID from URL if present
  const navigate = useNavigate()
  const isEditMode = Boolean(id)
  // const editAccess = boolean(id)
  const { authToken } = useContext(AuthContext)

  const location = useLocation()

  // Extract type from query string
  const searchParams = new URLSearchParams(location.search)

  const [formData, setFormData] = useState({
    title: '',
    type: '',
    subject: '',
    mail_for: '',
    body: '',
  })

  useEffect(() => {
    if (isEditMode) {
      // Fetch Template data to prefill the form
      axios
        .get(`${BASE_URL}/api/mail-templates/${id}`, {
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
          console.error('Error fetching Email Template data:', error)
          alert('Failed to fetch Email Template data.')
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
        // Update existing Template
        response = await axios.put(`${BASE_URL}/api/mail-templates/${id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      } else {
        // Create new Template
        response = await axios.post(`${BASE_URL}/api/mail-templates`, formData, {
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
              ? // ? `\n Template updated successfully.`
                ``
              : `\nUsername: ${response.data.data.username}\nCompany Name: ${response.data.data.company_name}`),
        )
        navigate('/settings/email_templates') // Redirect to Configs list after success
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
      title: '',
      type: '',
      subject: '',
      mail_for: '',
      body: '',
    })
    // if (isEditMode) {
    navigate('/settings/email_templates') // Redirect to config list if editing
    // }
  }

  return (
    <CRow>
      <CCol xs="8" md="8">
        <CForm onSubmit={handleSubmit}>
          {/* Basic Details Section */}
          <CRow>
            <CCol xs="12" md="12">
              <CCard className="mb-4">
                <CCardHeader>
                  <CCardTitle>
                    {isEditMode ? 'Edit Email Template' : 'Create Email Template'}
                  </CCardTitle>
                </CCardHeader>
                <CCardBody>
                  <CRow>
                    {/* <DefaultProductsComponent formData={formData} handleChange={handleChange} /> */}
                    {/* <DefaultCampaignComponent formData={formData} handleChange={handleChange} /> */}
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="title">
                        Template Name <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="title"
                        name="title"
                        value={formData.title}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="type">
                        Mail Type <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="type"
                        name="type"
                        value={formData.type}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="subject">
                        Subject <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="subject"
                        name="subject"
                        value={formData.subject}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="mail_for">
                        Email For <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormSelect
                        id="mail_for"
                        name="mail_for"
                        value={formData.mail_for}
                        onChange={handleChange}
                        required
                      >
                        <option key="" value="" disabled>
                          Select Assign Method
                        </option>
                        <option key="agent" value="agent">
                          Agent
                        </option>
                        <option key="request" value="request">
                          Request
                        </option>
                        <option key="instant" value="instant">
                          Instant
                        </option>
                        <option key="other" value="other">
                          Other
                        </option>
                      </CFormSelect>
                    </CCol>
                    <CCol md="12" className="mb-3">
                      <CFormLabel htmlFor="body">
                        Content <span style={{ color: 'red' }}>*</span>
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
                  </CRow>
                </CCardBody>
                {/* Buttons */}
                <CCardFooter>
                  <CRow>
                    <CCol xs="12" md="12" className="d-flex justify-content-end">
                      <CButton type="submit" color="primary" className="me-2">
                        {isEditMode ? 'Update' : 'Submit'}
                      </CButton>
                      <CButton type="button" color="secondary" onClick={handleCancel}>
                        Cancel
                      </CButton>
                    </CCol>
                  </CRow>
                </CCardFooter>
              </CCard>
            </CCol>
          </CRow>
        </CForm>
      </CCol>
      <CCol md={4} className="p-2">
        <CCard
          // style={{ width: 'auto', textAlign: 'center', borderRadius: '8px', overflow: 'hidden' }}
          style={{ width: 'auto', borderRadius: '8px', overflow: 'hidden' }}
        >
          {/* <CCardBody
            style={{
              backgroundColor: '#0d6efd',
              color: '#fff',
              padding: '2rem',
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              justifyContent: 'center',
              height: '250px',
            }}
          >
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
          </CCardBody> */}
          <CCardHeader>Template Preview</CCardHeader>
          <CCardBody>
            {/* <CCardTitle>first last</CCardTitle>
            <CCardText>mail</CCardText> */}
            <div dangerouslySetInnerHTML={{ __html: formData.body }} />
          </CCardBody>
          <CCardFooter style={{ padding: '1rem', display: 'flex', justifyContent: 'center' }}>
            {/* <CButton color="link" style={{ color: '#dc3545', textDecoration: 'none' }}>
              <CIcon icon={cilPencil} className="me-2" />
              Profile
            </CButton> */}
          </CCardFooter>
        </CCard>
      </CCol>
    </CRow>
  )
}

export default EmailTemplatesForm
