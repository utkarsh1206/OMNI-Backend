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
  const { authToken } = useContext(AuthContext)

  const [userGroups, setUserGroups] = useState([])
  const [users, setUsers] = useState([])
  const [categories, setCategories] = useState([])

  const [incidentType, setIncidentType] = useState([])

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
    incident_id: '',
    category_id: '',
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
        .then(async (response) => {
          const ticketData = response.data.data
          setFormData(ticketData)

          // Fetch user details for assign_to_id
          if (ticketData.assign_to_id) {
            try {
              const userResponse = await axios.get(
                `${BASE_URL}/api/users/${ticketData.assign_to_id}`,
                {
                  headers: {
                    'Content-Type': 'application/json',
                    Authorization: `Bearer ${authToken}`,
                  },
                },
              )

              const userGroupId = userResponse.data.data.usergroup_id // Assuming user data includes usergroup_id
              setFormData((prev) => ({
                ...prev,
                usergroup_id: userGroupId,
              }))
              fetchUsers(userGroupId)
            } catch (err) {
              console.error('Error fetching user details:', err)
            }
          }
        })
        .catch((error) => {
          console.error('Error fetching Support Ticket data:', error)
          alert('Failed to fetch Support Ticket data.')
        })
    }
  }, [id, isEditMode])

  // Fetch Category on component mount
  useEffect(() => {
    if (formData.campaign_id) {
      const fetchCategories = async () => {
        try {
          const response = await axios.get(
            `${BASE_URL}/api/categories?department_id=${formData.campaign_id}`,
            {
              headers: {
                'Content-Type': 'application/json',
                'Access-Control-Allow-Origin': '*',
                Authorization: `Bearer ${authToken}`,
              },
            },
          )
          setCategories(response.data.data)
        } catch (err) {
          setError('Error fetching Categories')
        }
      }
      fetchCategories()
    } else {
      setCategories([])
      setFormData((prevData) => ({
        ...prevData,
        category_id: '',
        incident_id: '',
      }))
      setIncidentType([])
    }
  }, [formData.campaign_id])

  // Fetch UserGroup on component mount
  useEffect(() => {
    const fetchUserGroups = async () => {
      // setLoadingOccupations(true)
      try {
        const response = await axios.get(`${BASE_URL}/api/usergroup?page=all`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        setUserGroups(response.data.data)
      } catch (err) {
        setError('Error fetching Users')
      }
    }
    fetchUserGroups()
  }, [])

  const fetchUsers = async (usergroupId) => {
    try {
      const response = await axios.get(`${BASE_URL}/api/users?usergroup=${usergroupId}`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authToken}`,
        },
      })
      setUsers(response.data.data)
    } catch (err) {
      console.error('Error fetching users:', err)
      setError('Error fetching Users')
    }
  }

  // Fetch Incident Types on component mount
  useEffect(() => {
    if (formData.campaign_id) {
      const fetchIncidentType = async () => {
        try {
          const response = await axios.get(
            `${BASE_URL}/api/incident-types?category_id=${formData.category_id}`,
            {
              headers: {
                'Content-Type': 'application/json',
                'Access-Control-Allow-Origin': '*',
                Authorization: `Bearer ${authToken}`,
              },
            },
          )
          setIncidentType(response.data.data)
        } catch (err) {
          setError('Error fetching Incident Types')
        }
      }
      fetchIncidentType()
    } else {
      setIncidentType([])
      setFormData((prevData) => ({
        ...prevData,
        incident_id: '',
      }))
    }
  }, [formData.category_id])

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
    }
  }, [formData.account_id])

  // const handleChange = (e) => {
  //   const { name, value } = e.target
  //   setFormData((prevData) => ({
  //     ...prevData,
  //     [name]: value,
  //   }))
  // }

  const handleChange = (e) => {
    const { name, value } = e.target

    setFormData((prevState) => {
      const updatedFormData = { ...prevState, [name]: value }

      // If the incident type changes, update the default priority
      if (name === 'incident_id') {
        const selectedIncident = incidentType.find((incident) => incident.id.toString() === value)
        // const selectedIncident = incidentType.find((incident) => incident.name === value);

        if (selectedIncident) {
          updatedFormData.default_priority = selectedIncident.priority || ''
        }
      }

      return updatedFormData
    })

    if (name === 'usergroup_id') {
      fetchUsers(value) // Fetch users for the selected user group
      setFormData((prev) => ({
        ...prev,
        assign_to_id: '', // Reset assign_to_id when usergroup changes
      }))
    }
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
      incident_id: '',
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
                <CCol md="4" className="mb-3">
                  <CFormLabel
                    htmlFor="account_id"
                    className="d-flex justify-content-between align-items-center"
                  >
                    <span>
                      Account <span style={{ color: 'red' }}>*</span>
                    </span>
                    {loadingAccounts && <small className="ms-auto">Loading Accounts...</small>}
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
                <CCol md="4" className="mb-3">
                  <CFormLabel
                    htmlFor="contact_id"
                    className="d-flex justify-content-between align-items-center"
                  >
                    <span>
                      Contact <span style={{ color: 'red' }}>*</span>
                    </span>
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
                <DefaultCampaignComponent formData={formData} handleChange={handleChange} />
                <CCol md="4" className="mb-3">
                  <CFormLabel
                    htmlFor="category_id"
                    className="d-flex justify-content-between align-items-center"
                  >
                    <span>
                      Category <span style={{ color: 'red' }}>*</span>
                    </span>
                    {/* {loadingContacts && <small>Loading Contacts...</small>} */}
                  </CFormLabel>
                  <CFormSelect
                    id="category_id"
                    name="category_id"
                    value={formData.category_id}
                    onChange={handleChange}
                  >
                    <option value="" disabled>
                      Select Category
                    </option>
                    {categories.map((category) => (
                      <option key={category.id} value={category.id}>
                        {category.name}
                      </option>
                    ))}
                  </CFormSelect>
                </CCol>
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="incident_id">
                    Incident Type <span style={{ color: 'red' }}>*</span>
                  </CFormLabel>
                  <CFormSelect
                    id="incident_id"
                    name="incident_id"
                    value={formData.incident_id}
                    onChange={handleChange}
                  >
                    <option value="" disabled>
                      Select Incident Type
                    </option>
                    {incidentType.map((incidentType) => (
                      <option key={incidentType.id} value={incidentType.id}>
                        {incidentType.id} - {incidentType.name}
                      </option>
                    ))}
                  </CFormSelect>
                </CCol>
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="usergroup_id">
                    User Group {/* <span style={{ color: 'red' }}>*</span> */}
                  </CFormLabel>
                  <CFormSelect
                    id="usergroup_id"
                    name="usergroup_id"
                    value={formData.usergroup_id}
                    onChange={handleChange}
                  >
                    <option value="">Select UserGroup</option>
                    {userGroups.map((userGroup) => (
                      <option key={userGroup.id} value={userGroup.id}>
                        {userGroup.name}
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
                    Type <span style={{ color: 'red' }}>*</span>
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
                    <option value="" disabled>
                      Select Priority
                    </option>
                    {Object.keys(PRIORITY_TO_COLOR).map((priority) => (
                      <option key={priority} value={priority}>
                        {priority}
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
                    {Object.keys(STATUS_TO_COLOR)
                      .filter((key) => isEditMode || (key !== 'Resolved' && key !== 'Closed'))
                      .map((key) => (
                        <option key={key} value={key}>
                          {key}
                        </option>
                      ))}
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
                  required
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
