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
  CFormSelect,
  CFormTextarea,
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

  const [users, setUsers] = useState([])
  const { authToken } = useContext(AuthContext)

  const [formData, setFormData] = useState({
    // Basic Details
    product_id: '',
    account_id: '',
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
    cc_mails: '',
    bcc_mails: '',
    description: '',
    attachment: '',
    status: 'Active',
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

  // Fetch Users on component mount
  useEffect(() => {
    const fetchUsers = async () => {
      // setLoadingOccupations(true)
      try {
        const response = await axios.get(`${BASE_URL}/api/users?page=all`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        setUsers(response.data.data)
        // setLoadingOccupations(false)
      } catch (err) {
        setError('Error fetching Users')
        // setLoadingOccupations(false)
      }
    }
    fetchUsers()
  }, [])

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
      account_id: '',
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
      cc_mails: '',
      bcc_mails: '',
      description: '',
      attachment: '',
      status: 'Active',
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
                 <FormGroup
                  label="Account"
                  name="account_id"
                  type="text"
                  value={formData.account_id}
                  onChange={handleChange}
                  required
                /> 
                <DefaultCampaignComponent formData={formData} handleChange={handleChange} />
                {/* <FormGroup
                  label="Inbound Group"
                  name="inbound_group_id"
                  type="text"
                  value={formData.inbound_group_id}
                  onChange={handleChange}
                  required
                /> */}

                <FormGroup
                  label="Contact"
                  name="contact_id"
                  type="text"
                  value={formData.contact_id}
                  onChange={handleChange}
                  required
                />
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="assign_to_id">
                    Assign To {/* <span style={{ color: 'red' }}>*</span> */}
                  </CFormLabel>
                  <CFormSelect
                    id="assign_to_id"
                    name="assign_to_id"
                    value={formData.assign_to_id}
                    onChange={handleChange}
                    // disabled={loadingCountries}
                  >
                    <option value="">Assign to User</option>
                    {users.map((user) => (
                      <option key={user.id} value={user.id}>
                        {user.first_name} {user.last_name}
                      </option>
                    ))}
                  </CFormSelect>
                </CCol>
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
                    <option value="" disabled>
                      Select Default Type
                    </option>
                    <option key="Problems" value="Problems">
                      Problems
                    </option>
                    <option key="Help" value="Help">
                      Help
                    </option>
                    <option key="Incident" value="Incident">
                      Incident
                    </option>
                    <option key="Service Request" value="Service Request">
                      Service Request
                    </option>
                    <option key="Service Delivery" value="Service Delivery">
                      Service Delivery
                    </option>
                    <option key="Service Assurance" value="Service Assurance">
                      Service Assurance
                    </option>
                    <option key="Questions" value="Questions">
                      Questions
                    </option>
                    <option key="Feature Request" value="Feature Request">
                      Feature Request
                    </option>
                    <option key="Other" value="Other">
                      Other
                    </option>
                    <option key="Change Management" value="Change Management">
                      Change Management
                    </option>
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
                    <option value="" disabled>
                      Select Default Priority
                    </option>
                    <option key="Medium" value="Medium">
                      Medium
                    </option>
                    <option key="Low" value="Low">
                      Low
                    </option>
                    <option key="High" value="High">
                      High
                    </option>
                    <option key="Critical" value="Critical">
                      Critical
                    </option>
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
                    <option value="" disabled>
                      Select Default Status
                    </option>
                    <option key="Unassigned" value="Unassigned">
                      Unassigned
                    </option>
                    <option key="Open" value="Open">
                      Open
                    </option>
                    <option key="Waiting on Customer" value="Waiting on Customer">
                      Waiting on Customer
                    </option>
                    <option key="Waiting on Third Party" value="Waiting on Third Party">
                      Waiting on Third Party
                    </option>
                    <option key="Resolved" value="Resolved">
                      Resolved
                    </option>
                    <option key="Closed" value="Closed">
                      Closed
                    </option>
                  </CFormSelect>
                </CCol>
              </CRow>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      <CRow className="mt-4">
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>Ticket Details</CCardHeader>
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
                  label="To Mail(s)"
                  name="to_emails"
                  type="text"
                  value={formData.to_emails}
                  onChange={handleChange}
                />
                <FormGroup
                  label="CC"
                  name="cc_mails"
                  type="text"
                  value={formData.cc_mails}
                  onChange={handleChange}
                />
                <FormGroup
                  label="BCC"
                  name="bcc_mails"
                  type="text"
                  value={formData.bcc_mails}
                  onChange={handleChange}
                />
                <CCol md="12" className="mb-3">
                  <CFormLabel htmlFor="description">
                    Description <span style={{ color: 'red' }}>*</span>
                  </CFormLabel>
                  <CFormTextarea
                    // type="textarea"
                    id="description"
                    name="description"
                    rows={5}
                    value={formData.description}
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
