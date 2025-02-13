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
  CFormCheck,
  CFormSelect,
  CFormTextarea,
  CAccordion,
  CAccordionItem,
  CAccordionHeader,
  CAccordionBody,
  CCardTitle,
  CCardFooter,
} from '@coreui/react'
import axios from 'axios'
import { useNavigate, useParams } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'
import DefaultCampaignComponent from '../campaign/DefaultCampaignComponent'
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

const AccountForm = () => {
  const { authToken } = useContext(AuthContext)
  const navigate = useNavigate()

  const { id } = useParams() // If you are in edit mode
  const isEditMode = Boolean(id)

  const [countries, setCountries] = useState([])
  const [states, setStates] = useState([])
  const [cities, setCities] = useState([])
  const [industryType, setIndustryTpye] = useState([])
  const [businessType, setBusinessTpye] = useState([])
  const [owner, setOwner] = useState([])

  const [loadingCountries, setLoadingCountries] = useState(false)
  const [loadingStates, setLoadingStates] = useState(false)
  const [loadingCities, setLoadingCities] = useState(false)

  const [error, setError] = useState('')

  const [formData, setFormData] = useState({
    product_id: '',
    campaign_id: '',
    name: '',
    mobile: '',
    website: '',
    industry_id: '',
    business_type_id: '',
    tax_number: '',
    country_id: '',
    state_id: '',
    city_id: '',
    pincode: '',
    address: '',
    owner_id: '',
    description: '',
    // product_deployed_type: '',
    // neox_integration: '',
    // phone_type: '',
    // phone_type_comment: '',
    // pstn_line: '',
    // pstn_ports_qty: '',
    // isp_details: '',
    // did_number_details: '',
    // pilot_number_detail: '',
    // extension_range_detail: '',
    // server_serialnumber: '',
    // server_ip: '',
    // server_mac: '',
    // license_key: '',
    // no_of_users: '',
    // account_expiry_date: '',
    // license_detail: '',
    company_name: '',
    contact_person_first_name: '',
    contact_person_last_name: '',
    contact_no: '',
    landline_no: '',
    company_email: '',
  })

  useEffect(() => {
    if (isEditMode) {
      // Fetch accounts data to prefill the form
      axios
        .get(`${BASE_URL}/api/accounts/${id}`, {
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
          alert('Failed to fetch Account data.\n' + error.response.data.message)
        })
    }
  }, [id, isEditMode])

  // Fetch countries list
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

  // Fetch Industry types
  useEffect(() => {
    const fetchIndustryType = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/api/industry_type?page=all`)
        setIndustryTpye(response.data.data)
      } catch (err) {
        setError('Error fetching Industry Types')
      }
    }
    fetchIndustryType()
  }, [])

  // Fetch Business types
  useEffect(() => {
    const fetchBusinessType = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/api/business_type?page=all`)

        setBusinessTpye(response.data.data)
      } catch (err) {
        setError('Error fetching Business Types')
      }
    }
    fetchBusinessType()
  }, [])

  // Fetch owners
  useEffect(() => {
    const fetchOwner = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/api/users?all_user=Y`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
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

  const validatePhoneNumber = (phone) => {
    const phonePattern = /^(\+?[0-9]{1,4})?(\s?[-.\(\)]?\(?\d{1,4}\)?[-.\s]?\d{1,4}[-.\s]?\d{1,4})$/
    return phonePattern.test(phone)
  }

  const handleSubmit = async (e) => {
    e.preventDefault()

    if (!authToken || !BASE_URL) {
      console.error('Missing auth token or API base URL.')
      setError('Missing auth token or API base URL.')
      return
    }

    const form = e.target
    if (form.checkValidity()) {
      try {
        let response
        if (isEditMode) {
          response = await axios.put(`${BASE_URL}/api/accounts/${id}`, formData, {
            headers: {
              'Content-Type': 'application/json',
              'Access-Control-Allow-Origin': '*',
              Authorization: `Bearer ${authToken}`,
            },
          })
        } else {
          response = await axios.post(`${BASE_URL}/api/accounts`, formData, {
            headers: {
              'Content-Type': 'application/json',
              'Access-Control-Allow-Origin': '*',
              Authorization: `Bearer ${authToken}`,
            },
          })
        }

        if (response.status === 200 || response.status === 201) {
          alert(response.data.message)
          navigate('/accounts')
        } else {
          console.error('Operation failed:', error)
          alert(error.response.data.message)
        }

        handleCancel()
      } catch (err) {
        console.error('Operation failed:', err)
        if (err.response) {
          if (err.response.status === 422) {
            const errorMessage =
              err.response.data.message || 'An error occurred while processing your request.'
            alert(errorMessage)
          } else {
            alert('An error occurred while processing your request.')
          }
        } else {
          alert('An error occurred while processing your request.')
        }
      }
    } else {
      form.reportValidity()
    }
  }

  const showIspDetails =
    formData.pstn_line === 'SIP' ||
    formData.pstn_line === 'Both' ||
    formData.pstn_line === 'Select PSTN Line'
  const showPstnPortsQty =
    formData.pstn_line === 'PRI' ||
    formData.pstn_line === 'Both' ||
    formData.pstn_line === 'Select PSTN Line'

  const handleCancel = () => {
    setFormData({
      product_id: '',
      campaign_id: '',
      name: '',
      mobile: '',
      website: '',
      industry_id: '',
      business_type_id: '',
      tax_number: '',
      country_id: '',
      state_id: '',
      city_id: '',
      pincode: '',
      address: '',
      owner_id: '',
      description: '',
      // product_deployed_type: '',
      // neox_integration: '',
      // phone_type: '',
      // phone_type_comment: '',
      // pstn_line: '',
      // pstn_ports_qty: '',
      // isp_details: '',
      // did_number_details: '',
      // pilot_number_detail: '',
      // extension_range_detail: '',
      // server_serialnumber: '',
      // server_ip: '',
      // server_mac: '',
      // license_key: '',
      // no_of_users: '',
      // account_expiry_date: '',
      // license_detail: '',
      company_name: '',
      contact_person_first_name: '',
      contact_person_last_name: '',
      contact_no: '',
      landline_no: '',
      company_email: '',
    })

    navigate('/accounts')
  }

  return (
    <CCard className="mb-4">
      <CCardHeader>
        <CCardTitle>Account</CCardTitle>
      </CCardHeader>
      <CCardBody>
        <CForm id="accountForm" onSubmit={handleSubmit}>
          <CAccordion flush activeItemKey={1}>
            <CAccordionItem itemKey={1}>
              <CAccordionHeader>Basic Detailing</CAccordionHeader>
              <CAccordionBody>
                {/* Basic Details Section */}
                <CRow>
                  <CCol xs="12" md="12">
                    <CRow>
                      <DefaultProductsComponent formData={formData} handleChange={handleChange} />
                      <DefaultCampaignComponent formData={formData} handleChange={handleChange} />
                      <FormGroup
                        label="Name"
                        name="name"
                        type="text"
                        value={formData.name}
                        onChange={handleChange}
                        required
                      />
                      <FormGroup
                        label="Phone Number"
                        name="mobile"
                        type="number"
                        value={formData.mobile}
                        onChange={handleChange}
                      />
                      <FormGroup
                        label="Website"
                        name="website"
                        type="text"
                        value={formData.website}
                        onChange={handleChange}
                      />
                      <CCol md="4" className="mb-3">
                        <CFormLabel htmlFor="industry_id">Industry</CFormLabel>
                        <CFormSelect
                          id="industry_id"
                          name="industry_id"
                          value={formData.industry_id}
                          onChange={handleChange}
                        >
                          <option value="">Select Industry</option>
                          {industryType.map((industry) => (
                            <option key={industry.id} value={industry.id}>
                              {industry.title}
                            </option>
                          ))}
                        </CFormSelect>
                      </CCol>
                      <CCol md="4" className="mb-3">
                        <CFormLabel htmlFor="business_type_id">Business Type</CFormLabel>
                        <CFormSelect
                          id="business_type_id"
                          name="business_type_id"
                          value={formData.business_type_id}
                          onChange={handleChange}
                        >
                          <option value="">Select Business Type</option>
                          {businessType.map((business) => (
                            <option key={business.id} value={business.id}>
                              {business.title}
                            </option>
                          ))}
                        </CFormSelect>
                      </CCol>
                      <FormGroup
                        label="Tax Number"
                        name="tax_number"
                        type="text"
                        value={formData.tax_number}
                        onChange={handleChange}
                      />
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
                      <CCol md="4" className="mb-3">
                        <CFormLabel htmlFor="owner_id">Owner</CFormLabel>
                        <CFormSelect
                          id="owner_id"
                          name="owner_id"
                          value={formData.owner_id}
                          onChange={handleChange}
                        >
                          <option value="">Select Owner</option>
                          {owner.map((owners) => (
                            <option key={owners.id} value={owners.id}>
                              {owners.first_name} {owners.last_name}
                            </option>
                          ))}
                        </CFormSelect>
                      </CCol>
                    </CRow>
                  </CCol>
                </CRow>
              </CAccordionBody>
            </CAccordionItem>
            {/* Neox Details Section */}
            {/* <CRow className="mt-4">
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>Neox Detailing</CCardHeader>
            <CCardBody>
              <CRow>
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="product_deployed_type">
                    Product Deployed Type <span style={{ color: 'red' }}>*</span>
                  </CFormLabel>
                  <CFormSelect
                    id="product_deployed_type"
                    name="product_deployed_type"
                    value={formData.product_deployed_type}
                    onChange={handleChange}
                  >
                    <option value="">Select Product Deployed Type</option>
                    <option key="IP-PBX" value="IP-PBX">IP-PBX</option>
                    <option key="Dial Center" value="Dial Center">Dial Center</option>
                    <option key="Both" value="Both">Both</option>
                  </CFormSelect>
                </CCol>
                <FormGroup
                  label="Neox Integration"
                  name="neox_integration"
                  type="radio"
                  required
                  options={[
                    { value: 'Yes', label: 'Yes' },
                    { value: 'No', label: 'No' }
                  ]}
                  value={formData.neox_integration}
                  onChange={handleChange}
                />
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="phone_type">
                    Phone Type <span style={{ color: 'red' }}>*</span>
                  </CFormLabel>
                  <CFormSelect
                    id="phone_type"
                    name="phone_type"
                    value={formData.phone_type}
                    onChange={handleChange}
                  >
                    <option value="">Select Phone Type</option>
                    <option key="IP Phone" value="IP Phone">IP Phone</option>
                    <option key="Analog Phone" value="Analog Phone">Analog Phone</option>
                    <option key="Both" value="Both">Both</option>
                  </CFormSelect>
                </CCol>
                <FormGroup
                  label="Phone Type Comment"
                  name="phone_type_comment"
                  type="textarea"
                />
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="pstn_line">
                    PSTN Line <span style={{ color: 'red' }}>*</span>
                  </CFormLabel>
                  <CFormSelect
                    id="pstn_line"
                    name="pstn_line"
                    value={formData.pstn_line}
                    onChange={handleChange}
                  >
                    <option value="">Select PSTN Line</option>
                    <option key="PRI" value="PRI">PRI</option>
                    <option key="SIP" value="SIP">SIP</option>
                    <option key="Both" value="Both">Both</option>
                  </CFormSelect>
                </CCol>
                {showIspDetails && (
                  <FormGroup
                    label="ISP Details"
                    name="isp_details"
                    type="text"
                    value={formData.isp_details}
                    onChange={handleChange}
                    required
                  />
                )}
                {showPstnPortsQty && (
                  <FormGroup
                    label="PSTN Ports Qty"
                    name="pstn_ports_qty"
                    type="text"
                    value={formData.pstn_ports_qty}
                    onChange={handleChange}
                    required
                  />
                )}
                <FormGroup
                  label="DID Number Details"
                  name="did_number_details"
                  type="textarea"
                  required
                  value={formData.did_number_details}
                  onChange={handleChange}
                />
                <FormGroup
                  label="Pilot Number Details"
                  name="pilot_number_detail"
                  type="textarea"
                  required
                  value={formData.pilot_number_detail}
                  onChange={handleChange}
                />
                <FormGroup
                  label="Extension Range Details"
                  name="extension_range_detail"
                  type="textarea"
                  required
                  value={formData.extension_range_detail}
                  onChange={handleChange}
                />
              </CRow>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow> */}

            {/* Server Details Section */}
            {/* <CRow className="mt-4">
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>Server Detailing</CCardHeader>
            <CCardBody>
              <CRow>
                <FormGroup
                  label="Server Serial Number"
                  name="server_serialnumber"
                  type="text"
                  required
                  value={formData.server_serialnumber}
                  onChange={handleChange}
                />
                <FormGroup
                  label="Server IP"
                  name="server_ip"
                  type="text"
                  required
                  value={formData.server_ip}
                  onChange={handleChange}
                />
                <FormGroup
                  label="Server MAC"
                  name="server_mac"
                  type="text"
                  required
                  value={formData.server_mac}
                  onChange={handleChange}
                />
              </CRow>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow> */}

            {/* License Details Section */}
            {/* <CRow className="mt-4">
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>License Detailing</CCardHeader>
            <CCardBody>
              <CRow>
                <FormGroup
                  label="License Key"
                  name="license_key"
                  type="text"
                  required
                  value={formData.license_key}
                  onChange={handleChange}
                />
                <FormGroup
                  label="Number of Contacts"
                  name="no_of_users"
                  type="text"
                  required
                  value={formData.no_of_users}
                  onChange={handleChange}
                />
                <FormGroup
                  label="Account Expiry Date"
                  name="account_expiry_date"
                  type="date"
                  required
                  value={formData.account_expiry_date}
                  onChange={handleChange}
                />
                <FormGroup
                  label="License Detail"
                  name="license_detail"
                  type="textarea"
                  required
                  value={formData.license_detail}
                  onChange={handleChange}
                />
              </CRow>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow> */}

            {/* Partner Details Section */}
            <CAccordionItem itemKey={2}>
              <CAccordionHeader>Partner Detailing</CAccordionHeader>
              <CAccordionBody>
                <CRow className="mt-4">
                  <CCol xs="12" md="12">
                    <CRow>
                      <FormGroup
                        label="Company Name"
                        name="company_name"
                        type="text"
                        required
                        value={formData.company_name}
                        onChange={handleChange}
                      />
                      <FormGroup
                        label="Contact Person First Name"
                        name="contact_person_first_name"
                        type="text"
                        required
                        value={formData.contact_person_first_name}
                        onChange={handleChange}
                      />
                      <FormGroup
                        label="Contact Person Last Name"
                        name="contact_person_last_name"
                        type="text"
                        required
                        value={formData.contact_person_last_name}
                        onChange={handleChange}
                      />
                      <FormGroup
                        label="Contact No."
                        name="contact_no"
                        type="number"
                        required
                        value={formData.contact_no}
                        onChange={handleChange}
                      />
                      <FormGroup
                        label="Landline No."
                        name="landline_no"
                        type="text"
                        value={formData.landline_no}
                        onChange={handleChange}
                      />
                      <FormGroup
                        label="Email"
                        name="company_email"
                        type="email"
                        required
                        value={formData.company_email}
                        onChange={handleChange}
                      />
                    </CRow>
                  </CCol>
                </CRow>
              </CAccordionBody>
            </CAccordionItem>

            <CAccordionItem itemKey={3}>
              <CAccordionHeader>Extra Information</CAccordionHeader>
              <CAccordionBody>
                <CRow className="mt-4">
                  <CCol xs="12" md="12">
                    <CRow>
                      {/* <FormGroup
                        label="Remarks"
                        name="description"
                        type="textarea"
                        value={formData.description}
                        onChange={handleChange}
                        style={{ width: '100%', height: '150px' }}
                      /> */}
                      <CCol md="12" className="mb-3">
                        <CFormLabel htmlFor="body">Remarks</CFormLabel>
                        <CFormTextarea
                          id="description"
                          name="description"
                          rows={5}
                          value={formData.description}
                          onChange={handleChange}
                          // required
                        />
                      </CCol>
                    </CRow>
                  </CCol>
                </CRow>
              </CAccordionBody>
            </CAccordionItem>
          </CAccordion>
        </CForm>
      </CCardBody>
      <CCardFooter>
        <CButton type="submit" color="primary" className="me-2" form="accountForm">
          {isEditMode ? 'Update' : 'Submit'}
        </CButton>
        <CButton type="button" color="secondary" onClick={handleCancel}>
          Cancel
        </CButton>
      </CCardFooter>
    </CCard>
  )
}

export default AccountForm
