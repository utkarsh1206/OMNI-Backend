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
  CInputGroup,
  CDropdown,
  CDropdownToggle,
  CDropdownMenu,
  CDropdownItem,
  CFormCheck
} from '@coreui/react'
import axios from 'axios'
import { useNavigate, useParams } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'
import DefaultProductsComponent from '../product/DefaultProductsComponent'

const FormGroup = ({ label, name, value, onChange, required, type, disabled, options, style }) => {
  if (type === 'textarea') {
    return (
      <CCol md="4" className="mb-3">
        <CFormLabel htmlFor={name}>
          {label} {required && <span style={{ color: 'red' }}>*</span>}
        </CFormLabel>
        <CFormTextarea
          id={name}
          name={name}
          value={value}
          onChange={onChange}
          required={required}
          disabled={disabled}
          style={style}
        />
      </CCol>
    )
  }

  // If the type is 'radio', render a group of radio buttons
  if (type === 'radio' && options) {
    return (
      <CCol md="4" className="mb-3">
        <CFormLabel htmlFor={name}>
          {label} {required && <span style={{ color: 'red' }}>*</span>}
        </CFormLabel>
        <div>
          {options.map((option) => (
            <div key={option.value} className="form-check-inline">
              <CFormCheck
                inline
                type="radio"
                id={`${name}_${option.value}`}
                name={name}
                value={option.value}
                checked={value === option.value}
                onChange={onChange}
                disabled={disabled}
              />
              <label htmlFor={`${name}_${option.value}`}>{option.label}</label>
            </div>
          ))}
        </div>
      </CCol>
    )
  }

  // Default case: for text, number, email, etc. - render a standard input
  return (
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
}

const ContactForm = () => {
  const { authToken } = useContext(AuthContext)
  const navigate = useNavigate()

  const { id } = useParams()  // If you are in edit mode
  const isEditMode = Boolean(id)

  const [countries, setCountries] = useState([])
  const [states, setStates] = useState([])
  const [cities, setCities] = useState([])
  const [loadingCountries, setLoadingCountries] = useState(false)
  const [loadingStates, setLoadingStates] = useState(false)
  const [loadingCities, setLoadingCities] = useState(false)

  const [error, setError] = useState("")
  const [accounts, setAccounts] = useState([])
  const [owner, setOwner] = useState([])

  const [formData, setFormData] = useState({
    product_id: '',
    account_id: '',
    name_prefix: '',
    first_name: '',
    last_name: '',
    email: '',
    mobile: '',
    owner_id: '',
    country_id: '',
    state_id: '',
    city_id: '',
    pincode: '',
    address: '',
    description: '',
    gender: '',
  })

  useEffect(() => {
    if (isEditMode) {
      // Fetch contacts data to prefill the form
      axios
        .get(`${BASE_URL}/api/contacts/${id}`, {
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
          console.error('Error fetching Account data:', error)
          alert('Failed to fetch Account data.')
        })
    }
  }, [id, isEditMode])

  // Fetch countries
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

  // Fetch Accounts
  useEffect(() => {
    const fetchAccounts = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/api/accounts?page=all`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        setAccounts(response.data.data)
      } catch (err) {
        setError('Error fetching Accounts')
      }
    }
    fetchAccounts()
  }, [])

  // Fetch Owners
  useEffect(() => {
    const fetchOwner = async () => {
      try {
        console.log("token", authToken)
        const response = await axios.get(`${BASE_URL}/api/users?all_user=Y`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        console.log("user ", response.data.data)
        setOwner(response.data.data)
      } catch (err) {
        setError('Error fetching Owner')
      }
    }
    fetchOwner()
  }, [])

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData((prevData) => ({
      ...prevData,
      [name]: value,
    }))
  }

  const handlePrefix = (value) => {
    setFormData((prevData) => ({
      ...prevData,
      name_prefix: value,
    }))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()

    const form = e.target;

    // Trigger HTML5 validation
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    // Custom validation for product_id - make sure it's selected
    const productField = form.querySelector('#product_id');
    if (!productField || !productField.value) {
      alert("Please select a product.");
      return;
    }

    // Custom validation for gender - make sure one of the radio buttons is selected
    const genderFields = form.querySelectorAll('[name="gender"]');
    const genderSelected = [...genderFields].some(input => input.checked);
    if (!genderSelected) {
      alert("Please select a gender.");
      return;
    }

    if (!authToken || !BASE_URL) {
      setError("Missing auth token or API base URL.");
      return;
    }

    try {
      let response;
      if (isEditMode) {
        response = await axios.put(`${BASE_URL}/api/contacts/${id}`, formData, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      } else {
        response = await axios.post(`${BASE_URL}/api/contacts`, formData, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      }

      if (response.status === 200 || response.status === 201) {
        alert(response.data.message)
        navigate('/contacts')

      } else {
        alert('An error occurred while processing your request.')
      }

      // handleCancel()
    } catch (err) {
      if (err.response) {
        if (err.response.status === 422) {
          const errorMessage = err.response.data.message || 'An error occurred while processing your request.';
          alert(errorMessage);
        } else {
          alert('An error occurred while processing your request.');
        }
      } else {
        alert('An error occurred while processing your request.');
      }
    }
  }

  const handleCancel = () => {
    setFormData({
      product_id: '',
      account_id: '',
      name_prefix: '',
      first_name: '',
      last_name: '',
      email: '',
      mobile: '',
      owner_id: '',
      country_id: '',
      state_id: '',
      city_id: '',
      pincode: '',
      address: '',
      description: '',
      gender: '',
    })
    navigate('/contacts')
  }

  return (
    <CForm onSubmit={handleSubmit}>
      {/* Basic Details Section */}
      <CRow>
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>Contacts</CCardHeader>
            <CCardBody>
              <CRow>
                <DefaultProductsComponent formData={formData} handleChange={handleChange} />
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="account_id">
                    Account <span style={{ color: 'red' }}>*</span>
                  </CFormLabel>
                  <CFormSelect
                    id="account_id"
                    name="account_id"
                    value={formData.account_id}
                    onChange={handleChange}
                    required
                  >
                    <option value="">Select Account</option>
                    {accounts.map((account) => (
                      <option key={account.id} value={account.id}>
                        {account.name}
                      </option>
                    ))}
                  </CFormSelect>
                </CCol>
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
                  required
                  value={formData.last_name}
                  onChange={handleChange}
                />
                <FormGroup
                  label="Email"
                  name="email"
                  type="email"
                  value={formData.email}
                  onChange={handleChange}
                />
                <FormGroup
                  label="Mobile"
                  name="mobile"
                  type="number"
                  required
                  value={formData.mobile}
                  onChange={handleChange}
                />
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="owner_id">
                    Owner
                  </CFormLabel>
                  <CFormSelect
                    id="owner_id"
                    name="owner_id"
                    value={formData.owner_id}
                    onChange={handleChange}
                  >
                    <option value="">Select Owner</option>
                    {owner.map((owners) => (
                      <option key={owners.id} value={owners.id}>
                        {owners.username}
                      </option>
                    ))}
                  </CFormSelect>
                </CCol>
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="country_id">
                    Country
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
                        State
                        {loadingStates && <small> Loading states...</small>}
                      </CFormLabel>
                      <CFormSelect
                        id="state_id"
                        name="state_id"
                        value={formData.state_id}
                        onChange={(e) => {
                          handleChange(e)
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
                        City
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
                  label="Pincode"
                  name="pincode"
                  type="text"
                  value={formData.pincode}
                  onChange={handleChange}
                />
                <FormGroup
                  label="Address"
                  name="address"
                  type="textarea"
                  value={formData.address}
                  onChange={handleChange}
                />
                <FormGroup
                  label="Description"
                  name="description"
                  type="textarea"
                  value={formData.description}
                  onChange={handleChange}
                />
                <FormGroup
                  label="Gender"
                  name="gender"
                  type="radio"
                  required
                  options={[
                    { value: 'Male', label: 'Male' },
                    { value: 'Female', label: 'Female' },
                    { value: 'Transgender', label: 'Transgender' }
                  ]}
                  value={formData.gender}
                  onChange={handleChange}
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
          <CButton type="button" color="secondary"
            onClick={handleCancel}
          >
            Cancel
          </CButton>
        </CCol>
      </CRow>
    </CForm>
  )
}

export default ContactForm