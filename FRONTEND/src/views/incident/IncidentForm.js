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
import { BASE_URL, PRIORITY_TO_COLOR } from '../../config'
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

const IncidentForm = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const isEditMode = Boolean(id)
  // const editAccess = boolean(id)
  const [department, setDepartment] = useState([])
  const [loadingDepartments, setLoadingDepartments] = useState(false)
  const [category, setCategory] = useState([])
  const [loadingCategory, setLoadingCategory] = useState(false)
  const [sla, setSla] = useState([])
  const [loadingSLA, setLoadingSLA] = useState(false)
  const { authToken } = useContext(AuthContext)

  const [formData, setFormData] = useState({
    // Basic Details
    name: '',
    description: '',
    department_id: '',
    category_id: '',
    priority: '',
    sla_id: '',
  })

  // Fetch department on component mount
  useEffect(() => {
    const fetchDepartments = async () => {
      setLoadingDepartments(true)
      try {
        const response = await axios.get(`${BASE_URL}/api/departments?page=all`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        setDepartment(response.data.data)
        setLoadingDepartments(false)
      } catch (err) {
        // setError('Error fetching Departments')
        setLoadingDepartments(false)
      }
    }
    fetchDepartments()
  }, [])

  useEffect(() => {
    const fetchSLA = async () => {
      setLoadingSLA(true)
      try {
        const response = await axios.get(`${BASE_URL}/api/sla-settings?page=all`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        setSla(response.data.data)
        setLoadingSLA(false)
      } catch (err) {
        // setError('Error fetching Departments')
        setLoadingSLA(false)
      }
    }
    fetchSLA()
  }, [])

  useEffect(() => {
    if (formData.department_id) {
      const fetchCategories = async () => {
        setLoadingCategory(true)
        try {
          const response = await axios.get(
            `${BASE_URL}/api/categories?department_id=${formData.department_id}&page=all`,
            {
              headers: {
                'Content-Type': 'application/json',
                'Access-Control-Allow-Origin': '*',
                Authorization: `Bearer ${authToken}`,
              },
            },
          )
          setCategory(response.data.data)
          setLoadingCategory(false)
        } catch (err) {
          //   setError('Error fetching states')
          setLoadingCategory(false)
        }
      }
      fetchCategories()
    } else {
      setCategory([])
      setFormData((prevData) => ({
        ...prevData,
        category_id: '',
        // city_id: '',
      }))
      //   setCities([])
    }
  }, [formData.department_id])

  useEffect(() => {
    if (isEditMode) {
      axios
        .get(`${BASE_URL}/api/incident-types/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        .then((response) => {
          const incidentData = response.data.data
          // Extract and set formData including department_id and category_id
          setFormData({
            name: incidentData.name,
            description: incidentData.description,
            department_id: incidentData.category.department.id, // Set department_id
            category_id: incidentData.category.id, // Set category_id
            priority: incidentData.priority,
            sla_id: incidentData.sla_settings.id, // Set SLA ID
          })
        })
        .catch((error) => {
          console.error('Error fetching Incident data:', error)
          alert('Failed to fetch Incident data.')
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
        response = await axios.put(`${BASE_URL}/api/incident-types/${id}`, formData, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      } else {
        response = await axios.post(`${BASE_URL}/api/incident-types`, formData, {
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
              ? `\nIncident updated successfully.`
              : `\nGroup: ${response.data.data.name}`),
        )
        navigate('/incdnt_type') // Redirect to Departments list after success
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
      name: '',
      description: '',
      department_id: '',
      category_id: '',
      priority: '',
    })
    // if (isEditMode) {
    navigate('/incdnt_type') // Redirect to userGroup list if editing
    // }
  }

  return (
    <CForm onSubmit={handleSubmit}>
      {/* Basic Details Section */}
      <CRow>
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>{isEditMode ? 'Edit Incident' : 'Create Incident'}</CCardHeader>
            <CCardBody>
              <CRow>
                <FormGroup
                  label="Incident Type"
                  name="name"
                  type="text"
                  value={formData.name}
                  onChange={handleChange}
                  required={!isEditMode} // Assuming username is not editable
                  disabled={isEditMode} // Assuming username is not editable
                />
                {/* <DefaultCampaignComponent formData={formData} handleChange={handleChange} /> */}
                <FormGroup
                  label="Description"
                  name="description"
                  type="text"
                  value={formData.description}
                  onChange={handleChange}
                  required
                />
                {/* <FormGroup
                  label="Department ID"
                  name="department_id"
                  type="text"
                  value={formData.department_id}
                  onChange={handleChange}
                  required
                /> */}
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="department_id">
                    Department <span style={{ color: 'red' }}>*</span>
                    {loadingDepartments && <small> Loading Departments...</small>}
                  </CFormLabel>
                  <CFormSelect
                    id="department_id"
                    name="department_id"
                    value={formData.department_id}
                    onChange={handleChange}
                    disabled={loadingDepartments}
                  >
                    <option value="">Select Department</option>
                    {department.map((department) => (
                      <option key={department.id} value={department.id}>
                        {department.name}
                      </option>
                    ))}
                  </CFormSelect>
                </CCol>
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="department_id">
                    Category <span style={{ color: 'red' }}>*</span>
                    {loadingCategory && <small> Loading Categories...</small>}
                  </CFormLabel>
                  <CFormSelect
                    id="category_id"
                    name="category_id"
                    value={formData.category_id}
                    onChange={handleChange}
                    disabled={loadingCategory}
                  >
                    <option value="">Select Category</option>
                    {category.map((category) => (
                      <option key={category.id} value={category.id}>
                        {category.name}
                      </option>
                    ))}
                  </CFormSelect>
                </CCol>

                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="priority">
                    Default Priority <span style={{ color: 'red' }}>*</span>
                  </CFormLabel>
                  <CFormSelect
                    id="priority"
                    name="priority"
                    value={formData.priority}
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
                  <CFormLabel htmlFor="department_id">
                    SLA <span style={{ color: 'red' }}>*</span>
                    {loadingSLA && <small> Loading SLA...</small>}
                  </CFormLabel>
                  <CFormSelect
                    id="sla_id"
                    name="sla_id"
                    value={formData.sla_id}
                    onChange={handleChange}
                    disabled={loadingSLA}
                  >
                    <option value="">Select SLA</option>
                    {sla.map((sla) => (
                      <option key={sla.id} value={sla.id}>
                        {sla.name}
                      </option>
                    ))}
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

export default IncidentForm
