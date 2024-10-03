import React, { useState } from 'react'
import axios from 'axios'
import {
  CButton,
  CCallout,
  CCard,
  CCardBody,
  CCol,
  CContainer,
  CForm,
  CFormInput,
  CFormLabel,
  CFormSelect,
  CImage,
  CInputGroup,
  CInputGroupText,
  CRow,
} from '@coreui/react'
import CIcon from '@coreui/icons-react'
import EyeOpenIcon from '../../../assets/images/icons/eye_open.svg'
import EyeClosedIcon from '../../../assets/images/icons/eye_closed.svg'
import { cilLockLocked, cilUser } from '@coreui/icons'
import { useNavigate } from 'react-router-dom'
import { BASE_URL } from '../../../config'

const Login = () => {
  const [userName, setUserName] = useState('')
  const [password, setPassword] = useState('')
  const [role, setRole] = useState('')
  const [domain, setDomain] = useState('')

  const [errorMsg, setErrorMsg] = useState('')

  const [showPassword, setShowPassword] = useState(false);

  const togglePasswordVisibility = () => {
    setShowPassword(!showPassword);
  };

  const navigate = useNavigate() // Initialize the navigate function

  const handleSubmit = async (e) => {
    e.preventDefault()

    setErrorMsg('')

    let TENANT_AUTH_USER = userName
    let TENANT_AUTH_PASS = password

    try {
      const response = await axios.post(
        // 'http://192.168.0.118:9090/tenant/api/login.php',
        `${BASE_URL}/tenant/api/login.php`,
        {
          TENANT_AUTH_USER,
          TENANT_AUTH_PASS,
        },
        {
          headers: {
            'Content-Type': 'application/json',
            // 'Access-Control-Allow-Origin': '*',
          },
          // withCredentials: true // if you are using cookies
        },
      )

      if (response.status == 200) {
        // Store the user's authentication token in local storage.
        if (response.data.token) {
          localStorage.setItem('authToken', response.data.token)

          console.log(response.data.code + ' ' + response.data.message)
          // alert(`Username: ${userName}\nPassword: ${password}`)
          navigate('/agent')
        } else {
          console.log('200 but some issue exists');
        }
      } else {
        // Handle the error.
        console.log('Some Error Occured')
      }
    } catch (error) {
      if (error.response.data.code === 1000001) {
        console.log(error.response.data.code + ' ' + error.response.data.message)
        {
          setErrorMsg('Wrong Username or Password')
        }
      } else {
        console.log(error.response.data.code + ' ' + error.response.data.message)
        setErrorMsg(error.response.data.message)
        // alert(error.response.data.message)
      }
      // }
    }
  }

  return (
    // <div className="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center">
    <div className="custom-backdrop">
      <div className="min-vh-100 d-flex flex-row align-items-center">
        <CContainer>
          <CRow className="justify-content-between">
            <CCol>
              <CCard lg={6} className="p-4">
                <CCardBody>
                  <CForm onSubmit={handleSubmit}>
                    <h1>Welcome!</h1>
                    <p className="text-body-secondary">Login to get started</p>
                    {errorMsg && (
                      <CCallout color="danger" style={{ color: '#e55353' }}>
                        {errorMsg}
                      </CCallout>
                    )}

                    <CInputGroup className="mb-2"><CFormLabel>Campaign</CFormLabel>
                      <CFormSelect
                        id="role"
                        value={role}
                        onChange={(e) => setRole(e.target.value)}
                      >
                        <option>--- Select Campaign ---</option>
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                        <option value="super-admin">Super Admin</option>
                      </CFormSelect>
                    </CInputGroup>
                    <CInputGroup className="mb-2">
                      <CInputGroupText>
                        <CIcon icon={cilUser} />
                      </CInputGroupText>
                      <CFormInput
                        type="text"
                        placeholder="Username"
                        value={userName}
                        onChange={(e) => setUserName(e.target.value)}
                        invalid={errorMsg !== ''}
                        autoComplete="username"
                        required
                        id="usernameinput"
                      />
                      <CInputGroupText>@</CInputGroupText>
                      <CFormInput
                        type="text"
                        placeholder="Domain"
                        value={domain}
                        onChange={(e) => setDomain(e.target.value)}
                        invalid={errorMsg !== ''}
                        autoComplete="domain"
                        required
                        id="domainInput"
                      />
                    </CInputGroup>
                    <CInputGroup className="mb-4">
                      <CInputGroupText>
                        <CIcon icon={cilLockLocked} />
                      </CInputGroupText>
                      <CFormInput
                        type={showPassword ? 'text' : 'password'}
                        placeholder="Password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        invalid={errorMsg !== ''}
                        autoComplete="current-password"
                        required
                        id="passwordinput"
                      />
                      <CInputGroupText onClick={togglePasswordVisibility} style={{ cursor: 'pointer' }}>
                        <img src={showPassword ? EyeClosedIcon : EyeOpenIcon} height={18} width={18} />
                      </CInputGroupText>
                    </CInputGroup>
                    <CRow>
                      <CCol xs={6}>
                        <CButton color="primary" className="px-4" type="submit">
                          Login
                        </CButton>
                      </CCol>
                      <CCol xs={6} className="text-end">
                        <CButton color="link" className="px-0">
                          Forgot password?
                        </CButton>
                      </CCol>
                    </CRow>
                  </CForm>
                </CCardBody>
              </CCard>
            </CCol>
            <CCol className='d-flex align-items-center justify-content-center'>
              <CCard className="text-white bg-transparent py-5 border-0">
                <CCardBody className="text-center">
                  <div>
                    <CImage src="src\assets\brand\new_logo.png" height={60} />
                    <p className="login-message">Calls and Messaging made easy</p>
                  </div>
                </CCardBody>
              </CCard>
            </CCol>
          </CRow>
        </CContainer>
      </div>
    </div>
  )
}

export default Login