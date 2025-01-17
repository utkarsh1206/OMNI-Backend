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

const CRONConfigsForm = () => {
  const { id } = useParams() // Get Config ID from URL if present
  const navigate = useNavigate()
  const isEditMode = Boolean(id)
  // const editAccess = boolean(id)
  const { authToken } = useContext(AuthContext)

  const [commands, setCommands] = useState([])
  const [loadingCommands, setLoadingCommands] = useState(true)

  const [expressions, setExpressions] = useState([])
  const [loadingExpressions, setLoadingExpressions] = useState(true)

  const location = useLocation()

  // Extract type from query string
  const searchParams = new URLSearchParams(location.search)
  const emailType = searchParams.get('type')

  const [formData, setFormData] = useState({
    command_signature: '',
    schedule_type: '',
    schedule_expression: '',
    // number_of_day: '',
    status: 'active',
  })

  // Fetch Commands on component mount
  useEffect(() => {
    const fetchCommands = async () => {
      setLoadingCommands(true)
      try {
        const response = await axios.get(`${BASE_URL}/api/cron-commands?page=all`)
        setCommands(response.data.data)
        setLoadingCommands(false)
      } catch (err) {
        // setError('Error fetching countries')
        setLoadingCommands(false)
      }
    }
    fetchCommands()
  }, [])

  // Fetch Expressions on component mount
  useEffect(() => {
    const fetchExpressions = async () => {
      setLoadingExpressions(true)
      try {
        const response = await axios.get(`${BASE_URL}/api/cron-expressions?page=all`)
        setExpressions(response.data.data)
        setLoadingExpressions(false)
      } catch (err) {
        // setError('Error fetching countries')
        setLoadingExpressions(false)
      }
    }
    fetchExpressions()
  }, [])

  useEffect(() => {
    if (isEditMode) {
      // Fetch Config data to prefill the form
      axios
        .get(`${BASE_URL}/api/cron-commands/${id}`, {
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
    const { name, value } = e.target

    // Check if Schedule Type is being changed
    // If schedule_type is changed
    if (name === 'schedule_type') {
      const selectedExpression = expressions.find(
        (expression) => expression.schedule_type === value, // Match schedule_type instead of id
      )
      if (selectedExpression) {
        setFormData((prevData) => ({
          ...prevData,
          [name]: value,
          schedule_expression: selectedExpression.schedule_expression, // Update schedule expression
        }))
      }
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
        response = await axios.put(`${BASE_URL}/api/cron-commands/${id}`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      } else {
        // Create new Config
        response = await axios.post(`${BASE_URL}/api/cron-commands`, formData, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      }

      if (response.status === 200) {
        alert(
          response.data.message +
            (isEditMode
              ? // ? `\n Config updated successfully.`
                ``
              : `\nUsername: ${response.data.data.username}\nCompany Name: ${response.data.data.company_name}`),
        )
        navigate('/settings/CRON_configs') // Redirect to Configs list after success
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
      command_signature: '',
      schedule_type: '',
      schedule_expression: '',
      // number_of_day: '',
      status: 'active',
    })
    // if (isEditMode) {
    navigate('/settings/cron_configs') // Redirect to config list if editing
    // }
  }

  return (
    <CRow>
      <CCol>
        <CForm onSubmit={handleSubmit}>
          {/* Basic Details Section */}
          <CRow>
            <CCol xs="12" md="12">
              <CCard className="mb-4">
                <CCardHeader>
                  <CCardTitle>{isEditMode ? 'Edit CRON Config' : 'Create CRON Config'}</CCardTitle>
                </CCardHeader>
                <CCardBody>
                  <CRow>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="command_signature">
                        Command Signature <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormSelect
                        id="command_signature"
                        name="command_signature"
                        value={formData.command_signature}
                        onChange={handleChange}
                        disabled={loadingCommands}
                        required
                      >
                        <option key="" value="" disabled>
                          Select Command
                        </option>
                        {commands.map((command) => (
                          <option key={command.id} value={command.id}>
                            {command.command_signature}
                          </option>
                        ))}
                      </CFormSelect>
                    </CCol>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="schedule_type">
                        Schedule Type <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormSelect
                        id="schedule_type"
                        name="schedule_type"
                        value={formData.schedule_type}
                        onChange={handleChange}
                        disabled={loadingExpressions}
                        required
                      >
                        <option key="" value="" disabled>
                          Select Schedule Type
                        </option>
                        {expressions.map((expression) => (
                          <option key={expression.id} value={expression.schedule_type}>
                            {expression.schedule_type}
                          </option>
                        ))}
                      </CFormSelect>
                    </CCol>
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="schedule_expression">
                        Schedule Expression <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="schedule_expression"
                        name="schedule_expression"
                        value={formData.schedule_expression}
                        onChange={handleChange}
                        disabled
                      />
                    </CCol>
                    {/* <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="number_of_day">
                        Number of Days <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormInput
                        type="text"
                        id="number_of_day"
                        name="number_of_day"
                        value={formData.number_of_day}
                        onChange={handleChange}
                        required
                      />
                    </CCol> */}
                    <CCol md="6" className="mb-3">
                      <CFormLabel htmlFor="status">
                        Status <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CFormSelect
                        id="status"
                        name="status"
                        value={formData.status}
                        onChange={handleChange}
                        required
                      >
                        <option key="active" value="active">
                          Active
                        </option>
                        <option key="inactive" value="inactive">
                          Inactive
                        </option>
                      </CFormSelect>
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
    </CRow>
  )
}

export default CRONConfigsForm
