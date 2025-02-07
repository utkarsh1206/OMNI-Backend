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
} from '@coreui/react'
import axios from 'axios'
import { useNavigate, useParams } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'
import DefaultCampaignComponent from '../campaign/DefaultCampaignComponent'

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

const UserGroupForm = () => {
  const { id } = useParams() // Get UserGroup ID from URL if present
  const navigate = useNavigate()
  const isEditMode = Boolean(id)
  // const editAccess = boolean(id)
  const { authToken } = useContext(AuthContext)

  const [formData, setFormData] = useState({
    // Basic Details
    name: '',
    campaign_id: '',
    description: '',
    status: 'Active',
  })

  useEffect(() => {
    if (isEditMode) {
      // Fetch tenant data to prefill the form
      axios
        .get(`${BASE_URL}/api/usergroup/${id}`, {
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
          console.error('Error fetching UserGroup data:', error)
          alert('Failed to fetch UserGroup data.\n' + error.response.data.message)
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
        response = await axios.put(`${BASE_URL}/api/usergroup/${id}`, formData, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      } else {
        // Create new tenant
        response = await axios.post(`${BASE_URL}/api/usergroup`, formData, {
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
              ? `\nUserGroup updated successfully.`
              : `\nGroup: ${response.data.data.name}`),
        )
        navigate('/user/user_groups') // Redirect to UserGroups list after success
      }
      handleCancel()
    } catch (error) {
      console.error('Operation failed:', error)
      alert(error.response.data.message)
    }
  }

  const handleCancel = () => {
    // Reset form or handle cancellation as needed
    setFormData({
      name: '',
      campaign_id: '',
      description: '',
      status: '',
    })
    // if (isEditMode) {
    navigate('/user/user_groups') // Redirect to userGroup list if editing
    // }
  }

  return (
    <CForm onSubmit={handleSubmit}>
      {/* Basic Details Section */}
      <CRow>
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>{isEditMode ? 'Edit User Group' : 'Create User Group'}</CCardHeader>
            <CCardBody>
              <CRow>
                <FormGroup
                  label="User Group Name"
                  name="name"
                  type="text"
                  value={formData.name}
                  onChange={handleChange}
                  required={!isEditMode} // Assuming username is not editable
                  disabled={isEditMode} // Assuming username is not editable
                />
                <DefaultCampaignComponent formData={formData} handleChange={handleChange} />
                <FormGroup
                  label="Description"
                  name="description"
                  type="text"
                  value={formData.description}
                  onChange={handleChange}
                  required
                />

                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="status">
                    Status <span style={{ color: 'red' }}>*</span>
                  </CFormLabel>
                  <CFormSelect
                    id="status"
                    name="status"
                    value={formData.status}
                    onChange={handleChange}
                  >
                    <option key="Active" value="Active">
                      Active
                    </option>
                    <option key="Inactive" value="Inactive">
                      Inactive
                    </option>
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

export default UserGroupForm
