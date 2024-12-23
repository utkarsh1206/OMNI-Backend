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
import { BASE_URL, PRIORITY_TO_COLOR, STATUS_TO_COLOR, TYPE_TO_COLOR } from '../../config'
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

  const [accounts, setAccounts] = useState([])
  const [loadingAccounts, setLoadingAccounts] = useState(false)
  const [contacts, setContacts] = useState([])
  const [loadingContacts, setLoadingContacts] = useState(false)

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
    cc: '',
    bcc: '',
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

  // Fetch Accounts when product_id changes
  useEffect(() => {
    if (formData.product_id) {
      const fetchAccounts = async () => {
        setLoadingAccounts(true)
        try {
          const response = await axios.get(
            `${BASE_URL}/api/accounts?product_id=${formData.product_id}&page=all`,
            {
              headers: {
                'Content-Type': 'application/json',
                'Access-Control-Allow-Origin': '*',
                Authorization: `Bearer ${authToken}`,
              },
            },
          )
          setAccounts(response.data.data)
          setLoadingAccounts(false)
        } catch (err) {
          // setError('Error fetching accounts')
          setLoadingAccounts(false)
        }
      }
      fetchAccounts()
    } else {
      setAccounts([])
      setFormData((prevData) => ({
        ...prevData,
        account_id: '',
        contact_id: '',
      }))
      // setCities([])
    }
  }, [formData.product_id])

  // Fetch Contacts when account_id changes
  useEffect(() => {
    if (formData.account_id) {
      const fetchContacts = async () => {
        setLoadingContacts(true)
        try {
          const response = await axios.get(
            `${BASE_URL}/api/contacts?account_id=${formData.account_id}&page=all`,
            {
              headers: {
                'Content-Type': 'application/json',
                'Access-Control-Allow-Origin': '*',
                Authorization: `Bearer ${authToken}`,
              },
            },
          )
          setContacts(response.data.data)
          setLoadingContacts(false)
        } catch (err) {
          // setError('Error fetching accounts')
          setLoadingContacts(false)
        }
      }
      fetchContacts()
    } else {
      setContacts([])
      setFormData((prevData) => ({
        ...prevData,
        contact_id: '',
      }))
      // setCities([])
    }
  }, [formData.account_id])

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
      cc: '',
      bcc: '',
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
                {/* <FormGroup
                  label="Account"
                  name="account_id"
                  type="text"
                  value={formData.account_id}
                  onChange={handleChange}
                  required
                /> */}
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="account_id">
                    Account <span style={{ color: 'red' }}>*</span>
                    {loadingAccounts && <small>Loading Accounts...</small>}
                  </CFormLabel>
                  <CFormSelect
                    id="account_id"
                    name="account_id"
                    value={formData.account_id}
                    onChange={handleChange}
                    disabled={loadingAccounts}
                  >
                    <option value="" disabled>
                      Select Account
                    </option>
                    {accounts.map((account) => (
                      <option key={account.id} value={account.id}>
                        {account.name}
                      </option>
                    ))}
                  </CFormSelect>
                </CCol>
                <DefaultCampaignComponent formData={formData} handleChange={handleChange} />

                {/* to be removed inbound group */}
                {/* <FormGroup
                  label="Inbound Group"
                  name="inbound_group_id"
                  type="text"
                  value={formData.inbound_group_id}
                  onChange={handleChange}
                  required
                /> */}

                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="contact_id">
                    Contact <span style={{ color: 'red' }}>*</span>
                    {loadingContacts && <small>Loading Contacts...</small>}
                  </CFormLabel>
                  <CFormSelect
                    id="contact_id"
                    name="contact_id"
                    value={formData.contact_id}
                    onChange={handleChange}
                    disabled={loadingContacts}
                  >
                    <option value="" disabled>
                      Select Contact
                    </option>
                    {contacts.map((contact) => (
                      <option key={contact.id} value={contact.id}>
                        {contact.first_name} {contact.last_name}
                      </option>
                    ))}
                  </CFormSelect>
                </CCol>
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
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="default_status">
                    Incident Type <span style={{ color: 'red' }}>*</span> <small>should be changed on camp select</small>
                  </CFormLabel>
                  <CFormSelect
                    id="default_status"
                    name="default_status"
                    // value={formData.default_status}
                    // onChange={handleChange}
                    // required
                  >
                    <option key="" value="" disabled>
                      Select Incident Type
                    </option>
                    {/* {Object.keys(STATUS_TO_COLOR).map((key) => (
                      <option key={key} value={key}>
                        {key}
                      </option>
                    ))} */}
                    <option>Type 1</option>
                    <option>Type 2</option>
                    <option>Type 3</option>
                    <option>Type 4</option>
                    <option>Type 5</option>
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
                  name="cc"
                  type="text"
                  value={formData.cc}
                  onChange={handleChange}
                />
                <FormGroup
                  label="BCC"
                  name="bcc"
                  type="text"
                  value={formData.bcc}
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
