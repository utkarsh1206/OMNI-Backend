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
import Login_User from './Login_User'

const Login = () => {
  const [username, setUsername] = useState('')
  const [password, setPassword] = useState('')
  const [role, setRole] = useState('')
  const [domain, setDomain] = useState('')
  const [errorMsg, setErrorMsg] = useState('')
  const [phone, setPhone] = useState('')
  const [phonePassword, setPhonePassword] = useState('')

  const [showPassword, setShowPassword] = useState(false);

  const togglePasswordVisibility = () => {
    setShowPassword(!showPassword);
  };

  const navigate = useNavigate() // Initialize the navigate function

  const handleSubmit = async (e) => {
    e.preventDefault()

    setErrorMsg('')

    let flag = role;
    let phone_password = phonePassword

    try {
      const response = await axios.post(
        `${BASE_URL}/api/login`,
        {
          username,
          password,
          flag,
          ...(domain && { domain }),
          ...(phone && { phone, phone_password }),
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
        localStorage.setItem('authToken', response.data.token)
        // alert(`Username: ${userName}\nPassword: ${password}`)
        navigate('/dashboard')
      } else {
        // Handle the error.
        console.log('Some Error Occured')
      }
    } catch (error) {
      setErrorMsg(error.response.data.error)
      console.error(error.response.data.error)
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

                    <CInputGroup className="mb-2">
                      <CFormSelect
                        id="role"
                        value={role}
                        required
                        onChange={(e) => { setRole(e.target.value); setDomain(""); setErrorMsg("") }}
                      >
                        <option value="">--- Select Role ---</option>
                        <option value="0">Super Admin</option>
                        <option value="1">Admin</option>
                        <option value="2">User</option>
                      </CFormSelect>
                    </CInputGroup>

                    <CInputGroup className="mb-2">
                      <CInputGroupText>
                        <CIcon icon={cilUser} />
                      </CInputGroupText>
                      <CFormInput
                        type="text"
                        placeholder="Username"
                        value={username}
                        onChange={(e) => setUsername(e.target.value)}
                        invalid={errorMsg !== ''}
                        autoComplete="username"
                        required
                        id="usernameinput"
                      />
                      {
                        (() => {
                          if (role == "0") {
                            return null;
                          } else {
                            return <>
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
                            </>
                          }
                        })()
                      }
                    </CInputGroup>
                    <CInputGroup className={role === "2" ? "mb-2" : "mb-4"}>
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

                    {role === "2" && (
                      <Login_User
                        phone={phone}
                        setPhone={setPhone}
                        phonePassword={phonePassword}
                        setPhonePassword={setPhonePassword}
                        userName={username}
                        setUserName={setUsername}
                        password={password}
                        setPassword={setPassword}
                      />
                    )}

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