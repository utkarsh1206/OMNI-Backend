// import React, { useContext, useEffect, useState } from 'react'
// import {
//   CButton,
//   CCard,
//   CCardBody,
//   CCardFooter,
//   CCardHeader,
//   CCardText,
//   CCardTitle,
//   CCol,
//   CContainer,
//   CRow,
// } from '@coreui/react'
// import { useNavigate } from 'react-router-dom'
// import { BASE_URL } from '../../config'
// import { cilPencil } from '@coreui/icons'
// import CIcon from '@coreui/icons-react'
// import { AuthContext } from '../../context/AuthContext'
// import axios from 'axios'

// const ViewProfile = () => {
//   const navigate = useNavigate()
//   const [profileData, setProfileData] = useState({
//     id: '',
//     type: '',
//     first_name: '',
//     last_name: '',
//     email: '',
//     username: '',
//     domain: '',
//     mobile: '',
//     status: '',
//     role_id: '',
//     usergroup_id: '',
//     name_prefix: '',
//     level: '',
//     date_of_birth: '',
//     country_id: '',
//     state_id: '',
//     city_id: '',
//     occupation_id: '',
//     pincode: '',
//     address: '',
//     gender: '',
//   })
//   const { authToken } = useContext(AuthContext)
//   // const profile_img = "src/assets/brand/new_menu_logo.png"
//   const profile_img = ''

//   useEffect(() => {
//     axios
//       .get(`${BASE_URL}/api/my-profile`, {
//         headers: {
//           'Content-Type': 'application/json',
//           Authorization: `Bearer ${authToken}`,
//         },
//       })
//       .then((response) => {
//         setProfileData(response.data.data.profile_info)
//       })
//       .catch((error) => {
//         console.error('Error fetching Users data:', error)
//         alert('Failed to fetch tenant data.')
//       })
//   })

//   return (
//     <>
//       <CContainer className="px-4">
//         <CRow xs={{ gutterX: 5 }}>
//           <CCol md={3} className="p-2">
//             <CCard
//               style={{
//                 // width: '18rem',
//                 width: 'auto',
//                 textAlign: 'center',
//                 borderRadius: '8px',
//                 overflow: 'hidden',
//               }}
//             >
//               <CCardBody
//                 style={{
//                   backgroundColor: profile_img ? '#f0f0f0' : '#0d6efd', // Background color for image and fallback
//                   color: '#fff',
//                   padding: '2rem',
//                   display: 'flex',
//                   flexDirection: 'column',
//                   alignItems: 'center',
//                   justifyContent: 'center',
//                   height: '250px', // Set height to make room for the background image
//                   backgroundImage: profile_img ? `url(${profile_img})` : 'none',
//                   backgroundSize: 'contain',
//                   backgroundPosition: 'center',
//                   backgroundRepeat: 'no-repeat',
//                 }}
//               >
//                 {/* Conditionally render the icon only if no profile image */}
//                 {!profile_img && (
//                   <div
//                     style={{
//                       width: '100px',
//                       height: '100px',
//                       backgroundColor: '#e0e0e0',
//                       borderRadius: '50%',
//                       display: 'flex',
//                       alignItems: 'center',
//                       justifyContent: 'center',
//                       fontSize: '24px',
//                       color: '#555',
//                       marginBottom: '1rem',
//                     }}
//                   >
//                     <CIcon icon={cilUser} size="xl" />
//                   </div>
//                 )}
//               </CCardBody>
//               <CCardBody>
//                 <CCardTitle>
//                   {profileData.first_name} {profileData.last_name}
//                 </CCardTitle>
//                 <CCardText>{profileData.email}</CCardText>
//               </CCardBody>
//               <CCardFooter style={{ padding: '1rem', display: 'flex', justifyContent: 'center' }}>
//                 <CButton color="link" style={{ color: '#dc3545', textDecoration: 'none' }}>
//                   <CIcon icon={cilUser} className="me-2" />
//                   Profile
//                 </CButton>
//               </CCardFooter>
//             </CCard>
//           </CCol>
//           <CCol className="p-2">
//             <CCard>
//               <CCardHeader className="d-flex justify-content-between">
//                 <CCardTitle>My Profile</CCardTitle>
//                 <CButton
//                   color="warning"
//                   size="sm"
//                   onClick={() => navigate(`/user/edit/${user.id}`)}
//                 >
//                   <CIcon icon={cilPencil} />
//                 </CButton>
//               </CCardHeader>
//               <CCardBody>
//                 <CRow>
//                   <CCol md="6" className="mb-4">
//                     First Name: {profileData.name_prefix} {profileData.first_name}
//                   </CCol>
//                   <CCol md="6" className="mb-4">
//                     Last Name: {profileData.last_name}
//                   </CCol>
//                   <CCol md="6" className="mb-4">
//                     Birthday: {profileData.date_of_birth}
//                   </CCol>
//                   <CCol md="6" className="mb-4">
//                     Occupation: {profileData.occupation_id}
//                   </CCol>
//                   <CCol md="6" className="mb-4">
//                     Email: {profileData.email}
//                   </CCol>
//                   <CCol md="6" className="mb-4">
//                     Mobile: {profileData.mobile}
//                   </CCol>
//                   <CCol md="6" className="mb-4">
//                     Country: {profileData.country_id}
//                   </CCol>
//                   <CCol md="6" className="mb-4">
//                     State: {profileData.state_id}
//                   </CCol>
//                   <CCol md="6" className="mb-4">
//                     City: {profileData.city_id}
//                   </CCol>
//                   <CCol md="6" className="mb-4">
//                     Address: {profileData.address}
//                   </CCol>
//                   <CCol md="6" className="mb-4">
//                     Pin/Zip Code: {profileData.pincode}
//                   </CCol>
//                 </CRow>
//               </CCardBody>
//             </CCard>
//           </CCol>
//         </CRow>
//       </CContainer>
//     </>
//   )
// }

// export default ViewProfile

// import React, { useContext, useEffect, useState } from 'react'
// import {
//   CButton,
//   CCard,
//   CCardBody,
//   CCardFooter,
//   CCardHeader,
//   CCardText,
//   CCardTitle,
//   CCol,
//   CContainer,
//   CRow,
//   CFormInput
// } from '@coreui/react'
// import { useNavigate } from 'react-router-dom'
// import { BASE_URL } from '../../config'
// import { cilPencil, cilSave } from '@coreui/icons'
// import CIcon from '@coreui/icons-react'
// import { AuthContext } from '../../context/AuthContext'
// import axios from 'axios'
// import { toCamelCase } from '../../utils/LetterCaseUtils'

// const ViewProfile = () => {
//   const navigate = useNavigate()
//   const [profileData, setProfileData] = useState({
//     id: '',
//     first_name: '',
//     last_name: '',
//     email: '',
//     mobile: '',
//     date_of_birth: '',
//     country_id: '',
//     state_id: '',
//     city_id: '',
//     occupation_id: '',
//     pincode: '',
//     address: ''
//   })
//   const [isEditing, setIsEditing] = useState(false) // Track edit mode
//   const { authToken } = useContext(AuthContext)

//   useEffect(() => {
//     axios
//       .get(`${BASE_URL}/api/my-profile`, {
//         headers: {
//           'Content-Type': 'application/json',
//           Authorization: `Bearer ${authToken}`,
//         },
//       })
//       .then((response) => {
//         setProfileData(response.data.data.profile_info)
//       })
//       .catch((error) => {
//         console.error('Error fetching Users data:', error)
//         alert('Failed to fetch tenant data.')
//       })
//   }, [authToken])

//   const handleInputChange = (e) => {
//     const { name, value } = e.target
//     setProfileData((prevData) => ({
//       ...prevData,
//       [name]: value,
//     }))
//   }

//   const handleSave = () => {
//     // Perform save operation, e.g., API call
//     axios
//       .put(`${BASE_URL}/api/my-profile`, profileData, {
//         headers: {
//           'Content-Type': 'application/json',
//           Authorization: `Bearer ${authToken}`,
//         },
//       })
//       .then(() => {
//         setIsEditing(false)
//       })
//       .catch((error) => {
//         console.error('Error saving profile data:', error)
//         alert('Failed to save profile data.')
//       })
//   }

//   return (
//     <CContainer className="px-4">
//       <CRow xs={{ gutterX: 5 }}>
//         <CCol md={3} className="p-2">
//           <CCard style={{ width: 'auto', textAlign: 'center', borderRadius: '8px', overflow: 'hidden' }}>
//             <CCardBody
//               style={{
//                 backgroundColor: '#0d6efd',
//                 color: '#fff',
//                 padding: '2rem',
//                 display: 'flex',
//                 flexDirection: 'column',
//                 alignItems: 'center',
//                 justifyContent: 'center',
//                 height: '250px'
//               }}
//             >
//               <div style={{
//                 width: '100px',
//                 height: '100px',
//                 backgroundColor: '#e0e0e0',
//                 borderRadius: '50%',
//                 display: 'flex',
//                 alignItems: 'center',
//                 justifyContent: 'center',
//                 fontSize: '24px',
//                 color: '#555',
//                 marginBottom: '1rem',
//               }}>
//                 <CIcon icon={cilPencil} size="xl" />
//               </div>
//             </CCardBody>
//             <CCardBody>
//               <CCardTitle>{profileData.first_name} {profileData.last_name}</CCardTitle>
//               <CCardText>{profileData.email}</CCardText>
//             </CCardBody>
//             <CCardFooter style={{ padding: '1rem', display: 'flex', justifyContent: 'center' }}>
//               <CButton color="link" style={{ color: '#dc3545', textDecoration: 'none' }}>
//                 <CIcon icon={cilPencil} className="me-2" />
//                 Profile
//               </CButton>
//             </CCardFooter>
//           </CCard>
//         </CCol>
//         <CCol className="p-2">
//           <CCard>
//             <CCardHeader className="d-flex justify-content-between">
//               <CCardTitle>My Profile</CCardTitle>
//               <CButton
//                 color="warning"
//                 size="sm"
//                 onClick={() => setIsEditing(!isEditing)} // Toggle edit mode
//               >
//                 <CIcon icon={cilPencil} />
//               </CButton>
//             </CCardHeader>
//             <CCardBody>
//               <CRow>
//                 {['first_name', 'last_name', 'date_of_birth', 'occupation_id', 'email', 'mobile', 'country_id', 'state_id', 'city_id', 'address', 'pincode'].map((field, index) => (
//                   <CCol md="6" className="mb-4" key={index}>
//                     {toCamelCase(field)}:
//                     {isEditing ? (
//                       <CFormInput
//                         type="text"
//                         name={field}
//                         value={profileData[field]}
//                         onChange={handleInputChange}
//                         placeholder={`Enter ${field.replace('_', ' ')}`}
//                       />
//                     ) : (
//                       profileData[field]
//                     )}
//                   </CCol>
//                 ))}
//               </CRow>
//               {isEditing && (
//                 <CButton color="success" onClick={handleSave}>
//                   <CIcon icon={cilSave} className="me-2" /> Save
//                 </CButton>
//               )}
//             </CCardBody>
//           </CCard>
//         </CCol>
//       </CRow>
//     </CContainer>
//   )
// }

// export default ViewProfile

import React, { useContext, useEffect, useState } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardFooter,
  CCardHeader,
  CCardTitle,
  CCol,
  CContainer,
  CRow,
  CFormInput,
  CCardText,
  CFormSelect,
} from '@coreui/react'
import { useNavigate } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { cilPencil, cilSave, cilUser } from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../context/AuthContext'
import axios from 'axios'
import { toCamelCase } from '../../utils/LetterCaseUtils'

const ViewProfile = () => {
  const navigate = useNavigate()
  const [profileData, setProfileData] = useState({
    id: '',
    first_name: '',
    last_name: '',
    email: '',
    mobile: '',
    date_of_birth: '',
    country_id: '',
    state_id: '',
    city_id: '',
    occupation_id: '',
    pincode: '',
    address: '',
  })
  const [isEditing, setIsEditing] = useState(false) // Track edit mode
  const { authToken } = useContext(AuthContext)

  const [countries, setCountries] = useState([])
  const [states, setStates] = useState([])
  const [cities, setCities] = useState([])

  const [loadingCountries, setLoadingCountries] = useState(false)
  const [loadingStates, setLoadingStates] = useState(false)
  const [loadingCities, setLoadingCities] = useState(false)

  useEffect(() => {
    axios
      .get(`${BASE_URL}/api/my-profile`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authToken}`,
        },
      })
      .then((response) => {
        setProfileData(response.data.data.profile_info)
      })
      .catch((error) => {
        console.error('Error fetching Users data:', error)
        alert('Failed to fetch tenant data.')
      })
  }, [authToken])

  const handleInputChange = (e) => {
    const { name, value } = e.target
    setProfileData((prevData) => ({
      ...prevData,
      [name]: value,
    }))
  }

  const handleSave = () => {
    // Perform save operation, e.g., API call
    axios
      .put(`${BASE_URL}/api/my-profile`, profileData, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authToken}`,
        },
      })
      .then(() => {
        setIsEditing(false)
      })
      .catch((error) => {
        console.error('Error saving profile data:', error)
        alert('Failed to save profile data.')
      })
  }

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
    if (profileData.country_id) {
      const fetchStates = async () => {
        setLoadingStates(true)
        try {
          const response = await axios.get(
            `${BASE_URL}/api/states?country_id=${profileData.country_id}&page=all`,
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
      setProfileData((prevData) => ({
        ...prevData,
        state_id: '',
        city_id: '',
      }))
      setCities([])
    }
  }, [profileData.country_id])

  // Fetch cities when state_id changes
  useEffect(() => {
    if (profileData.state_id) {
      const fetchCities = async () => {
        setLoadingCities(true)
        try {
          const response = await axios.get(
            `${BASE_URL}/api/cities?state_id=${profileData.state_id}&page=all`,
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
      setProfileData((prevData) => ({
        ...prevData,
        city_id: '',
      }))
    }
  }, [profileData.state_id])

  return (
    <CContainer className="px-4">
      <CRow xs={{ gutterX: 5 }}>
        <CCol md={3} className="p-2">
          <CCard
            style={{ width: 'auto', textAlign: 'center', borderRadius: '8px', overflow: 'hidden' }}
          >
            <CCardBody
              style={{
                backgroundColor: '#0d6efd',
                color: '#fff',
                padding: '2rem',
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                height: '250px',
              }}
            >
              <div
                style={{
                  width: '100px',
                  height: '100px',
                  backgroundColor: '#e0e0e0',
                  borderRadius: '50%',
                  display: 'flex',
                  alignItems: 'center',
                  justifyContent: 'center',
                  fontSize: '24px',
                  color: '#555',
                  marginBottom: '1rem',
                }}
              >
                <CIcon icon={cilUser} size="xl" />
              </div>
            </CCardBody>
            <CCardBody>
              <CCardTitle>
                {profileData.first_name} {profileData.last_name}
              </CCardTitle>
              <CCardText>{profileData.email}</CCardText>
            </CCardBody>
            <CCardFooter style={{ padding: '1rem', display: 'flex', justifyContent: 'center' }}>
              <CButton color="link" style={{ color: '#dc3545', textDecoration: 'none' }}>
                <CIcon icon={cilPencil} className="me-2" />
                Profile
              </CButton>
            </CCardFooter>
          </CCard>
        </CCol>
        <CCol className="p-2">
          <CCard>
            <CCardHeader className="d-flex justify-content-between">
              <CCardTitle>My Profile</CCardTitle>
              <CButton
                color="warning"
                size="sm"
                onClick={() => setIsEditing(!isEditing)} // Toggle edit mode
              >
                <CIcon icon={cilPencil} />
              </CButton>
            </CCardHeader>
            <CCardBody>
              <CRow>
                {/* {Object.keys(profileData).map((field, index) => (
                  // Check if field is not `id` or other fields you want to skip
                  (field !== 'id') && (
                    <CCol md="6" className="mb-4" key={index}>
                      {toCamelCase(field)}: 
                      {isEditing ? (
                        <CFormInput
                          type="text"
                          name={field}
                          value={profileData[field] || ''}
                          onChange={handleInputChange}
                          placeholder={`Enter ${field.replace('_', ' ')}`}
                        />
                      ) : (
                        profileData[field] || 'N/A'
                      )}
                    </CCol>
                  )
                ))} */}
                <CCol md="6" className="mb-3 d-flex justify-content-between">
                  <CCol xs="4" className="align-content-center">
                    First Name:{' '}
                  </CCol>
                  <CCol>
                    {profileData.name_prefix}
                    {isEditing ? (
                      <CFormInput
                        type="text"
                        name="first_name"
                        value={profileData.first_name || ''}
                        onChange={handleInputChange}
                        placeholder={`Enter First Name`}
                      />
                    ) : (
                      profileData.first_name || 'N/A'
                    )}
                  </CCol>
                </CCol>
                <CCol md="6" className="mb-3 d-flex justify-content-between">
                  <CCol xs="4" className="align-content-center">
                    Last Name:{' '}
                  </CCol>
                  <CCol>
                    {isEditing ? (
                      <CFormInput
                        type="text"
                        name="last_name"
                        value={profileData.last_name || ''}
                        onChange={handleInputChange}
                        placeholder={`Enter Last Name`}
                      />
                    ) : (
                      profileData.last_name || 'N/A'
                    )}
                  </CCol>
                </CCol>
                <CCol md="6" className="mb-3 d-flex justify-content-between">
                  <CCol xs="4" className="align-content-center">
                    Birthday:{' '}
                  </CCol>
                  <CCol>
                    {isEditing ? (
                      <CFormInput
                        type="text"
                        name="date_of_birth"
                        value={profileData.date_of_birth || ''}
                        onChange={handleInputChange}
                        placeholder={`yyyy-mm-dd`}
                      />
                    ) : (
                      profileData.date_of_birth || 'N/A'
                    )}
                  </CCol>
                </CCol>
                <CCol md="6" className="mb-3 d-flex justify-content-between">
                  <CCol xs="4" className="align-content-center">
                    Occupation:{' '}
                  </CCol>
                  <CCol>
                    {isEditing ? (
                      <CFormInput
                        type="text"
                        name="occupation_id"
                        value={profileData.occupation_id || ''}
                        onChange={handleInputChange}
                        placeholder={`Enter Occupation`}
                      />
                    ) : (
                      profileData.occupation_id || 'N/A'
                    )}
                  </CCol>
                </CCol>
                <CCol md="6" className="mb-3 d-flex justify-content-between">
                  <CCol xs="4" className="align-content-center">
                    Email:{' '}
                  </CCol>
                  <CCol>
                    {isEditing ? (
                      <CFormInput
                        type="text"
                        name="email"
                        value={profileData.email || ''}
                        onChange={handleInputChange}
                        placeholder={`Enter Email`}
                      />
                    ) : (
                      profileData.email || 'N/A'
                    )}
                  </CCol>
                </CCol>
                <CCol md="6" className="mb-3 d-flex justify-content-between">
                  <CCol xs="4" className="align-content-center">
                    Mobile:{' '}
                  </CCol>
                  <CCol>
                    {isEditing ? (
                      <CFormInput
                        type="text"
                        name="mobile"
                        value={profileData.mobile || ''}
                        onChange={handleInputChange}
                        placeholder={`Enter Mobile`}
                      />
                    ) : (
                      profileData.mobile || 'N/A'
                    )}
                  </CCol>
                </CCol>
                <CCol md="6" className="mb-3 d-flex justify-content-between">
                  <CCol xs="4" className="align-content-center">
                    Country:{' '}
                  </CCol>
                  <CCol>
                    {isEditing ? (
                      // <CFormInput
                      //   type="text"
                      //   name="country_id"
                      //   value={profileData.country_id || ''}
                      //   onChange={handleInputChange}
                      //   placeholder={`Enter Country`}
                      // />
                      <CFormSelect
                        id="country_id"
                        name="country_id"
                        value={profileData.country_id}
                        onChange={handleInputChange}
                        disabled={loadingCountries}
                      >
                        <option value="">Select Country</option>
                        {countries.map((country) => (
                          <option key={country.id} value={country.id}>
                            {country.country_name}
                          </option>
                        ))}
                      </CFormSelect>
                    ) : (
                      profileData.country_id || 'N/A'
                    )}
                  </CCol>
                </CCol>
                <CCol md="6" className="mb-3 d-flex justify-content-between">
                  <CCol xs="4" className="align-content-center">
                    State:{' '}
                  </CCol>
                  <CCol>
                    {isEditing ? (
                      // <CFormInput
                      //   type="text"
                      //   name="state_id"
                      //   value={profileData.state_id || ''}
                      //   onChange={handleInputChange}
                      //   placeholder={`Enter State`}
                      // />
                      <CFormSelect
                        id="state_id"
                        name="state_id"
                        value={profileData.state_id}
                        onChange={(e) => {
                          handleInputChange(e)
                          // Reset city when state changes
                          setProfileData((prevData) => ({
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
                    ) : (
                      profileData.state_id || 'N/A'
                    )}
                  </CCol>
                </CCol>
                <CCol md="6" className="mb-3 d-flex justify-content-between">
                  <CCol xs="4" className="align-content-center">
                    City:{' '}
                  </CCol>
                  <CCol>
                    {isEditing ? (
                      // <CFormInput
                      //   type="text"
                      //   name="city_id"
                      //   value={profileData.city_id || ''}
                      //   onChange={handleInputChange}
                      //   placeholder={`Enter City`}
                      // />
                      <CFormSelect
                        id="city_id"
                        name="city_id"
                        value={profileData.city_id}
                        onChange={handleInputChange}
                        disabled={loadingCities}
                      >
                        <option value="">Select City</option>
                        {cities.map((city) => (
                          <option key={city.id} value={city.id}>
                            {city.city_name}
                          </option>
                        ))}
                      </CFormSelect>
                    ) : (
                      profileData.city_id || 'N/A'
                    )}
                  </CCol>
                </CCol>
                <CCol md="6" className="mb-3 d-flex justify-content-between">
                  <CCol xs="4" className="align-content-center">
                    Address:{' '}
                  </CCol>
                  <CCol>
                    {isEditing ? (
                      <CFormInput
                        type="text"
                        name="address"
                        value={profileData.address || ''}
                        onChange={handleInputChange}
                        placeholder={`Enter Address`}
                      />
                    ) : (
                      profileData.address || 'N/A'
                    )}
                  </CCol>
                </CCol>
                <CCol md="6" className="mb-3 d-flex justify-content-between">
                  <CCol xs="4" className="align-content-center">
                    Pin/Zip Code:{' '}
                  </CCol>
                  <CCol>
                    {isEditing ? (
                      <CFormInput
                        type="text"
                        name="pincode"
                        value={profileData.pincode || ''}
                        onChange={handleInputChange}
                        placeholder={`Enter Last Name`}
                      />
                    ) : (
                      profileData.pincode || 'N/A'
                    )}
                  </CCol>
                </CCol>
              </CRow>
              {isEditing && (
                <CButton color="success" onClick={handleSave}>
                  <CIcon icon={cilSave} className="me-2" /> Save
                </CButton>
              )}
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>
    </CContainer>
  )
}

export default ViewProfile
