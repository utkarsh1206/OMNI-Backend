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

const EmailConfigSettingsForm = () => {
  const { id } = useParams() // Get Config ID from URL if present
  const navigate = useNavigate()
  const isEditMode = Boolean(id)
  // const editAccess = boolean(id)
  const { authToken } = useContext(AuthContext)

  const location = useLocation()

  // Extract type from query string
  const searchParams = new URLSearchParams(location.search)
  const emailType = searchParams.get('type')

  const [formData, setFormData] = useState({
    type: emailType,
    product_id: '',
    campaign_id: '',
    // inbound_id: '',
    smtp_host: '',
    smtp_port: '',
    smtp_user: '',
    smtp_password: '',
    default_type: '',
    default_status: '',
    imap_host: '',
    imap_port: '',
    imap_user: '',
    imap_password: '',
    sender_name: '',
    sender_email: '',
    assign_method: '',
    signature: '',
  })

  useEffect(() => {
    if (isEditMode) {
      // Fetch Config data to prefill the form
      axios
        .get(`${BASE_URL}/api/email-configure/${id}`, {
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
          console.error('Error fetching Email Config data:', error)
          alert('Failed to fetch Email Config data.')
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
        // Update existing Config
        // console.log(JSON.stringify(formData, null, 2))
        response = await axios.put(`${BASE_URL}/api/email-configure/${id}`, formData, {
          headers: {
            // 'Content-Type': 'multipart/form-data',
            'Content-Type': 'appplication/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      } else {
        // Create new Config
        response = await axios.post(`${BASE_URL}/api/email-configure`, formData, {
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
          isEditMode
            ? // ? `\n Config updated successfully.`
              `Update Successful.`
            : `\nType: ${response.data.data.type}\nSMTP UserName: ${response.data.data.smtp_user}\nIMAP UserName: ${imap_user}`,
        )
        navigate('/settings/email_config_settings') // Redirect to Configs list after success
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
      type: emailType,
      product_id: '',
      campaign_id: '',
      // inbound_id: '',
      smtp_host: '',
      smtp_port: '',
      smtp_user: '',
      smtp_password: '',
      default_type: '',
      default_status: '',
      imap_host: '',
      imap_port: '',
      imap_user: '',
      imap_password: '',
      sender_name: '',
      sender_email: '',
      assign_method: '',
      signature: '',
    })
    // if (isEditMode) {
    navigate('/settings/email_config_settings') // Redirect to config list if editing
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
                    {isEditMode ? 'Edit Email Config' : 'Create Email Config'}
                  </CCardTitle>
                </CCardHeader>
                <CCardBody>
                  <CRow>
                    <DefaultProductsComponent formData={formData} handleChange={handleChange} />
                    <DefaultCampaignComponent formData={formData} handleChange={handleChange} />
                    <CCol md="4" className="mb-3">
                      <CFormLabel htmlFor="inbound_id">
                        Inbound Group <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="inbound_id"
                        name="inbound_id"
                        value={formData.inbound_id}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="smtp_host">
                        SMTP Host <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="smtp_host"
                        name="smtp_host"
                        value={formData.smtp_host}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="smtp_port">
                        SMTP Port <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="smtp_port"
                        name="smtp_port"
                        value={formData.smtp_port}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="smtp_user">
                        SMTP User <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="smtp_user"
                        name="smtp_user"
                        value={formData.smtp_user}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="smtp_password">
                        SMTP Password <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="smtp_password"
                        name="smtp_password"
                        value={formData.smtp_password}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="6" className="mb-3">
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
                    <CCol md="6" className="mb-3">
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
                          Select Type
                        </option>
                        {Object.keys(STATUS_TO_COLOR).map((key) => (
                          <option key={key} value={key}>
                            {key}
                          </option>
                        ))}
                      </CFormSelect>
                    </CCol>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="imap_host">
                        IMAP Host <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="imap_host"
                        name="imap_host"
                        value={formData.imap_host}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="imap_port">
                        IMAP Port <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="imap_port"
                        name="imap_port"
                        value={formData.imap_port}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="imap_user">
                        IMAP User <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="imap_user"
                        name="imap_user"
                        value={formData.imap_user}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="imap_password">
                        IMAP Password <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="imap_password"
                        name="imap_password"
                        value={formData.imap_password}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="4" className="mb-3">
                      <CFormLabel htmlFor="sender_name">
                        Sender Name <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="sender_name"
                        name="sender_name"
                        value={formData.sender_name}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="4" className="mb-3">
                      <CFormLabel htmlFor="sender_email">
                        Sender Email <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="sender_email"
                        name="sender_email"
                        value={formData.sender_email}
                        onChange={handleChange}
                        required
                      />
                    </CCol>
                    <CCol md="4" className="mb-3">
                      <CFormLabel htmlFor="assign_method">
                        Assign Method <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormSelect
                        id="assign_method"
                        name="assign_method"
                        value={formData.assign_method}
                        onChange={handleChange}
                        required
                      >
                        <option key="" value="" disabled>
                          Select Assign Method
                        </option>
                        <option key="random" value="random">
                          Random
                        </option>
                        <option key="round-robin" value="round_robin">
                          Round-Robin
                        </option>
                      </CFormSelect>
                    </CCol>
                    <CCol md="12" className="mb-3">
                      <CFormLabel htmlFor="signature">
                        Signature <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormTextarea
                        // type="textarea"
                        id="signature"
                        name="signature"
                        rows={5}
                        value={formData.signature}
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
          style={{ width: 'auto', textAlign: 'center', borderRadius: '8px', overflow: 'hidden' }}
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
          <CCardHeader>Configuration Helps</CCardHeader>
          <CCardBody>
            <CCardTitle>first last</CCardTitle>
            <CCardText>mail</CCardText>
          </CCardBody>
          <CCardFooter style={{ padding: '1rem', display: 'flex', justifyContent: 'center' }}>
            <CButton color="link" style={{ color: '#dc3545', textDecoration: 'none' }}>
              <CIcon icon={cilPencil} className="me-2" />
              Profile
            </CButton>
          </CCardFooter>
        </CCard>
      </CCol>
    </CRow>
  )
}

export default EmailConfigSettingsForm
