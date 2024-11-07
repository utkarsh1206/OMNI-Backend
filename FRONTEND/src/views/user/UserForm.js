import {
  CButton,
  CCol,
  CForm,
  CFormLabel,
  CFormInput,
  CFormSelect,
  CRow,
  CCard,
  CCardHeader,
  CCardTitle,
  CCardBody,
  CAccordion,
  CAccordionItem,
  CAccordionHeader,
  CAccordionBody,
  CCardFooter,
  CInputGroup,
  CDropdown,
  CDropdownItem,
  CDropdownMenu,
  CDropdownToggle,
} from '@coreui/react'
import axios from 'axios'
import React, { useContext, useEffect, useState } from 'react'
import { BASE_URL } from '../../config'
import { useNavigate, useParams } from 'react-router-dom'
import { AuthContext } from '../../context/AuthContext'

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

const UserForm = ({ visible, setVisible, type }) => {
  const { id } = useParams()
  const navigate = useNavigate()
  const isEditMode = Boolean(id)

  const { authToken } = useContext(AuthContext)

  const [countries, setCountries] = useState([])
  const [states, setStates] = useState([])
  const [cities, setCities] = useState([])

  const [loadingCountries, setLoadingCountries] = useState(false)
  const [loadingStates, setLoadingStates] = useState(false)
  const [loadingCities, setLoadingCities] = useState(false)

  const [error, setError] = useState('')

  const [formData, setFormData] = useState({
    tenant_id: '',
    first_name: '',
    last_name: '',
    email: '',
    username: '',
    password: '',
    phone: '',
    phone_password: '',
    // domain: '',
    mobile: '',
    status: '',
    is_delete: 'No',
    role_id: '',
    name_prefix: '',
    level: '',
    date_of_birth: '',
    country_id: '',
    state_id: '',
    city_id: '',
    occupation_id: '',
    pincode: '',
    address: '',
    gender: '',
  })

  // Fetch countries on component mount
  useEffect(() => {
    const fetchCountries = async () => {
      setLoadingCountries(true)
      try {
        const response = await axios.get(`${BASE_URL}/api/countries?page=all`)
        setCountries(response.data.data)
        setLoadingCountries(false)
      } catch (err) {
        setError('Error fetching countries')
        setLoadingCountries(false)
      }
    }
    fetchCountries()
  }, [])

  // Fetch states when country_id changes
  useEffect(() => {
    if (formData.country_id) {
      const fetchStates = async () => {
        setLoadingStates(true)
        try {
          const response = await axios.get(
            `${BASE_URL}/api/states?country_id=${formData.country_id}&page=all`,
          )
          setStates(response.data.data)
          setLoadingStates(false)
        } catch (err) {
          setError('Error fetching states')
          setLoadingStates(false)
        }
      }
      fetchStates()
    } else {
      setStates([])
      setFormData((prevData) => ({
        ...prevData,
        state_id: '',
        city_id: '',
      }))
      setCities([])
    }
  }, [formData.country_id])

  // Fetch cities when state_id changes
  useEffect(() => {
    if (formData.state_id) {
      const fetchCities = async () => {
        setLoadingCities(true)
        try {
          const response = await axios.get(
            `${BASE_URL}/api/cities?state_id=${formData.state_id}&page=all`,
          )
          setCities(response.data.data)
          setLoadingCities(false)
        } catch (err) {
          setError('Error fetching cities')
          setLoadingCities(false)
        }
      }
      fetchCities()
    } else {
      setCities([])
      setFormData((prevData) => ({
        ...prevData,
        city_id: '',
      }))
    }
  }, [formData.state_id])

  const handlePrefix = (value) => {
    setFormData((prevData) => ({
      ...prevData,
      name_prefix: value,
    }))
  }

  useEffect(() => {
    if (isEditMode) {
      // Fetch users data to prefill the form
      axios
        .get(`${BASE_URL}/api/users/${id}`, {
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
          console.error('Error fetching Users data:', error)
          alert('Failed to fetch tenant data.')
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

  // const handleSubmit = (event) => {
  const handleSubmit = async (e) => {
    e.preventDefault()
    try {
      let response
      if (isEditMode) {
        response = await axios.put(`${BASE_URL}/api/users/${id}`, formData, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      } else {
        response = await axios.post(`${BASE_URL}/api/users`, formData, {
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
              ? '' // '\nUser updated successfully.'
              : // : `\nUser: ${response.data.data.username}\nCompany Name: ${response.data.data.company_name}`)
                '\nUser Created Succesfully'),
        )
        navigate('/user/users')
      }
      handleCancel()
    } catch (error) {
      console.error('Operation failed: ', error)
      alert('An error occurred while processing your request.')
    }
  }

  const handleCancel = () => {
    navigate('/user/users')
  }

  return (
    <>
      <CCard className="mb-4">
        <CCardHeader>
          <CCardTitle>Create User</CCardTitle>
        </CCardHeader>
        <CCardBody>
          <CForm id="userForm" onSubmit={handleSubmit}>
            <CAccordion flush activeItemKey={1}>
              <CAccordionItem itemKey={1}>
                <CAccordionHeader>Personal Information</CAccordionHeader>
                <CAccordionBody>
                  <CRow>
                    <CCol md="4" className="mb-3">
                      <CFormLabel htmlFor="name">
                        First Name <span style={{ color: 'red' }}>*</span>
                      </CFormLabel>
                      <CInputGroup>
                        <CDropdown variant="input-group">
                          <CDropdownToggle color="secondary" variant="outline">
                            {(formData.name_prefix && formData.name_prefix) || 'Prefix'}
                          </CDropdownToggle>
                          <CDropdownMenu>
                            <CDropdownItem onClick={() => handlePrefix('Mr.')}>Mr.</CDropdownItem>
                            <CDropdownItem onClick={() => handlePrefix('Miss.')}>
                              Miss.
                            </CDropdownItem>
                            <CDropdownItem onClick={() => handlePrefix('Mrs.')}>Mrs.</CDropdownItem>
                          </CDropdownMenu>
                        </CDropdown>
                        <CFormInput
                          type="text"
                          id="first_name"
                          name="first_name"
                          value={formData.first_name}
                          onChange={handleChange}
                          required
                        />
                      </CInputGroup>
                    </CCol>
                    <FormGroup
                      label="Last Name"
                      name="last_name"
                      type="text"
                      value={formData.last_name}
                      onChange={handleChange}
                      required
                    />
                    <FormGroup
                      label="Date Of Birth"
                      name="date_of_birth"
                      type="text"
                      value={formData.date_of_birth}
                      onChange={handleChange}
                      required
                    />
                    <FormGroup
                      label="Gender"
                      name="gender"
                      type="text"
                      value={formData.gender}
                      onChange={handleChange}
                      required
                    />
                  </CRow>
                </CAccordionBody>
              </CAccordionItem>
              <CAccordionItem itemKey={2}>
                <CAccordionHeader>Account Information</CAccordionHeader>
                <CAccordionBody>
                  <CRow>
                    <FormGroup
                      label="Email"
                      name="email"
                      type="text"
                      value={formData.email}
                      onChange={handleChange}
                      required
                    />
                    <FormGroup
                      label="Username"
                      name="username"
                      type="text"
                      value={formData.username}
                      onChange={handleChange}
                      required
                    />
                    <FormGroup
                      label="Password"
                      name="password"
                      type="text"
                      value={formData.password}
                      onChange={handleChange}
                      required
                    />
                    <FormGroup
                      label="Role ID"
                      name="role_id"
                      type="text"
                      value={formData.role_id}
                      onChange={handleChange}
                      required
                    />
                  </CRow>
                </CAccordionBody>
              </CAccordionItem>
              <CAccordionItem itemKey={3}>
                <CAccordionHeader>Contact Information</CAccordionHeader>
                <CAccordionBody>
                  <CRow>
                    <FormGroup
                      label="Mobile"
                      name="mobile"
                      type="text"
                      value={formData.mobile}
                      onChange={handleChange}
                      required
                    />
                    <CCol md="4" className="mb-3">
                      <CFormLabel htmlFor="country_id">
                        Country <span style={{ color: 'red' }}>*</span>
                        {loadingCountries && <small> Loading countries...</small>}
                      </CFormLabel>
                      <CFormSelect
                        id="country_id"
                        name="country_id"
                        value={formData.country_id}
                        onChange={handleChange}
                        disabled={loadingCountries}
                      >
                        <option value="">Select Country</option>
                        {countries.map((country) => (
                          <option key={country.id} value={country.id}>
                            {country.country_name}
                          </option>
                        ))}
                      </CFormSelect>
                    </CCol>
                    {formData.country_id && (
                      <>
                        <CCol md="4" className="mb-3">
                          <CFormLabel htmlFor="state_id">
                            State <span style={{ color: 'red' }}>*</span>
                            {loadingStates && <small> Loading states...</small>}
                          </CFormLabel>
                          <CFormSelect
                            id="state_id"
                            name="state_id"
                            value={formData.state_id}
                            onChange={(e) => {
                              handleChange(e)
                              // Reset city when state changes
                              setFormData((prevData) => ({
                                ...prevData,
                                state_id: e.target.value,
                                city_id: '',
                              }))
                            }}
                            disabled={loadingStates}
                          >
                            <option value="">Select State</option>
                            {states.map((state) => (
                              <option key={state.id} value={state.id}>
                                {state.state_name}
                              </option>
                            ))}
                          </CFormSelect>
                        </CCol>

                        <CCol md="4" className="mb-3">
                          <CFormLabel htmlFor="city_id">
                            City <span style={{ color: 'red' }}>*</span>
                            {loadingCities && <small> Loading cities...</small>}
                          </CFormLabel>
                          <CFormSelect
                            id="city_id"
                            name="city_id"
                            value={formData.city_id}
                            onChange={handleChange}
                            disabled={loadingCities}
                          >
                            <option value="">Select City</option>
                            {cities.map((city) => (
                              <option key={city.id} value={city.id}>
                                {city.city_name}
                              </option>
                            ))}
                          </CFormSelect>
                        </CCol>
                      </>
                    )}
                    <FormGroup
                      label="Address"
                      name="address"
                      type="text"
                      value={formData.address}
                      onChange={handleChange}
                      required
                    />
                    <FormGroup
                      label="Pincode"
                      name="pincode"
                      type="text"
                      value={formData.pincode}
                      onChange={handleChange}
                      required
                    />
                  </CRow>
                </CAccordionBody>
              </CAccordionItem>
              <CAccordionItem itemKey={4}>
                <CAccordionHeader>Other Details</CAccordionHeader>
                <CAccordionBody>
                  <CRow>
                    <FormGroup
                      label="Status"
                      name="status"
                      type="text"
                      value={formData.status}
                      onChange={handleChange}
                      required
                    />
                    <FormGroup
                      label="Level"
                      name="level"
                      type="text"
                      value={formData.level}
                      onChange={handleChange}
                      required
                    />

                    <FormGroup
                      label="Occupation"
                      name="occupation_id"
                      type="text"
                      value={formData.occupation_id}
                      onChange={handleChange}
                      required
                    />
                  </CRow>
                </CAccordionBody>
              </CAccordionItem>
            </CAccordion>
          </CForm>
        </CCardBody>

        <CCardFooter>
          <CButton className="mx-2" type="submit" color="primary" form="userForm">
            Submit
          </CButton>
          <CButton color="secondary" onClick={handleCancel}>
            Cancel
          </CButton>
        </CCardFooter>
      </CCard>
      {error && <div style={{ color: 'red', padding: '10px' }}>{error}</div>}
    </>
  )
}

export default UserForm
