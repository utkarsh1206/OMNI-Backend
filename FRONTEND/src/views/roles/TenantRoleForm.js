import React, { useEffect, useState } from 'react'
import axios from 'axios'
import { useNavigate, useParams } from 'react-router-dom'
import { BASE_URL } from '../../config'
import {
  CButton,
  CCard,
  CCardBody,
  CCardFooter,
  CCardHeader,
  CCol,
  CContainer,
  CFormCheck,
  CFormInput,
  CFormLabel,
  CFormSelect,
  CRow,
  CSpinner,
} from '@coreui/react'
import { toCamelCase } from '../../utils/LetterCaseUtils'

// const DynamicForm = ({ isEditMode = false }) => {
const DynamicForm = () => {
  const { id } = useParams() // Get ID from URL for edit mode
  const isEditMode = Boolean(id)

  const [roles, setRoles] = useState({})
  const [loading, setLoading] = useState(true)
  const [name, setName] = useState('')
  const [description, setDescription] = useState('')
  const [status, setStatus] = useState('Active')

  const navigate = useNavigate()

  useEffect(() => {
    const fetchRoles = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/api/tenantrole-list`)
        const initializedRoles = {}
        for (const group in response.data.data) {
          initializedRoles[group] = response.data.data[group].reduce((acc, permission) => {
            acc[permission] = false // Initially set all permissions as unchecked
            return acc
          }, {})
        }
        return initializedRoles
      } catch (error) {
        console.error('Error fetching the roles:', error)
        return {}
      }
    }

    const fetchDataForEdit = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/api/tenantrole/${id}`)
        const roleData = response.data.data
        setName(roleData.name)
        setDescription(roleData.description || '')
        setStatus(roleData.status)

        const parsedPermissions = JSON.parse(roleData.permission)
        return parsedPermissions
      } catch (error) {
        console.error('Error fetching role data for edit:', error)
        return {}
      }
    }

    const initializeRoles = async () => {
      setLoading(true)
      try {
        const defaultRoles = await fetchRoles()
        let updatedRoles = defaultRoles

        if (isEditMode && id) {
          const rolePermissions = await fetchDataForEdit()

          // Merge default roles with the role's specific permissions
          for (const group in defaultRoles) {
            if (rolePermissions[group]) {
              for (const permission in defaultRoles[group]) {
                updatedRoles[group][permission] = rolePermissions[group][permission] === 1
              }
            }
          }
        }
        setRoles(updatedRoles)
      } catch (error) {
        console.error('Error initializing roles:', error)
      } finally {
        setLoading(false)
      }
    }

    initializeRoles()
  }, [id, isEditMode])

  const handleChange = (group, permission) => {
    setRoles((prevRoles) => ({
      ...prevRoles,
      [group]: {
        ...prevRoles[group],
        [permission]: !prevRoles[group][permission],
      },
    }))
  }

  const handleSubmit = async (event) => {
    event.preventDefault()

    const permissions = {}
    for (const group in roles) {
      permissions[group] = {}
      for (const permission in roles[group]) {
        permissions[group][permission] = roles[group][permission] ? 1 : 0
      }
    }

    const data = {
      name,
      permission: JSON.stringify(permissions),
      description,
      status,
    }

    try {
      let response
      if (isEditMode) {
        response = await axios.put(`${BASE_URL}/api/tenantrole/${id}`, data)
      } else {
        response = await axios.post(`${BASE_URL}/api/tenantrole`, data)
      }

      if (response.status === 200) {
        alert(
          response.data.message +
            (isEditMode
              ? '' // : `\n User: ${response.data.data.username}\nCompany Name: ${response.data.data.company_name}`)
              : '\nRole Created Succesfully'),
        )
        navigate('/user/users')
      }
      handleCancel()
    } catch (error) {
      console.error('Error submitting data:', error)
      // alert('An error occurred while processing your request.')
      if (error.response && error.response.data) {
        const errorData = error.response.data
        let errorMessage = ''
        for (const key in errorData) {
          if (Array.isArray(errorData[key])) {
            errorData[key].forEach((msg) => {
              errorMessage += `${msg}\n`
            })
          } else {
            errorMessage += `${errorData[key]}\n`
          }
        }
        alert(errorMessage)
      } else if (error.request) {
        alert('No response received from the server.')
      } else {
        alert('An error occurred while processing your request.')
      }
    }
  }

  const handleCancel = () => {
    navigate('/tenants/tenant_role')
  }

  if (loading) {
    return (
      <div className="text-center">
        <CSpinner color="primary" />
        <p>Loading...</p>
      </div>
    )
  }

  return (
    <CContainer className="mb-4">
      <CCard>
        <CCardHeader>
          <h4>{isEditMode ? 'Edit Role' : 'Create Role'}</h4>
        </CCardHeader>
        <form onSubmit={handleSubmit}>
          <CCardBody>
            <CRow>
              <CCol md="4" className="mb-3">
                <CFormLabel htmlFor="name">
                  Name <span style={{ color: 'red' }}>*</span>
                </CFormLabel>
                <CFormInput
                  type="text"
                  id="name"
                  name="Name"
                  value={name}
                  onChange={(e) => setName(e.target.value)}
                />
              </CCol>
              <CCol md="4" className="mb-3">
                <CFormLabel htmlFor="description">
                  Description <span style={{ color: 'red' }}>*</span>
                </CFormLabel>
                <CFormInput
                  type="text"
                  id="description"
                  name="Description"
                  value={description}
                  onChange={(e) => setDescription(e.target.value)}
                  required
                />
              </CCol>
              <CCol md="4" className="mb-3">
                <CFormLabel htmlFor="status">
                  Status <span style={{ color: 'red' }}>*</span>
                </CFormLabel>
                <CFormSelect
                  id="status"
                  name="status"
                  value={status}
                  onChange={(e) => setStatus(e.target.value)}
                >
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </CFormSelect>
              </CCol>
            </CRow>
            <hr />
            {Object.keys(roles).map((group) => (
              <CRow className="mb-3" key={group}>
                <CCol md="2">
                  <CFormLabel>{toCamelCase(group)}</CFormLabel>
                </CCol>
                <CCol className="d-flex">
                  <CRow>
                    {Object.keys(roles[group]).map((permission) => (
                      <CCol key={permission}>
                        <CFormCheck
                          checked={roles[group][permission]}
                          onChange={() => handleChange(group, permission)}
                          label={toCamelCase(permission)}
                        />
                      </CCol>
                    ))}
                  </CRow>
                </CCol>
              </CRow>
            ))}
          </CCardBody>
          <CCardFooter>
            <CButton className="mx-2" type="submit" color="primary">
              {isEditMode ? 'Update' : 'Submit'}
            </CButton>
            <CButton color="secondary" onClick={handleCancel}>
              Cancel
            </CButton>
          </CCardFooter>
        </form>
      </CCard>
    </CContainer>
  )
}

export default DynamicForm
