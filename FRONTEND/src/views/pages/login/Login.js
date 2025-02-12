import React, { useContext, useState } from 'react'
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
import new_logo from '../../../assets/brand/new_logo.png'
import { cilLockLocked, cilUser } from '@coreui/icons'
import { Navigate, redirect, useNavigate } from 'react-router-dom'
import { BASE_URL } from '../../../config'
import Login_User from './Login_User'
import { AuthContext } from '../../../context/AuthContext'

const Login = () => {
  const { login, authToken } = useContext(AuthContext)
  const navigate = useNavigate()

  const [credentials, setCredentials] = useState({
    username: '',
    password: '',
    domain: '',
    phone: '',
    phone_password: '',
    flag: '',
  })

  const [showPassword, setShowPassword] = useState(false)

  const togglePasswordVisibility = () => {
    setShowPassword(!showPassword)
  }

  const [errorMsg, setErrorMsg] = useState('')

  if (authToken) {
    // return <Redirect to="/dashboard" />
    return <Navigate to="/dashboard" replace />
  }

  const handleChange = (e) => {
    setCredentials({ ...credentials, [e.target.name]: e.target.value })
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setErrorMsg('')
    try {
      await login(credentials)
      if (credentials.flag == '2') {
        navigate('/agent')
      } else {
        navigate('/dashboard')
      }
    } catch (err) {
      if (err.response.data.error) {
        setErrorMsg(err.response.data.error)
      } else {
        setErrorMsg('Invalid email or password')
      }
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
                        name="flag"
                        value={credentials.flag}
                        required
                        onChange={handleChange}
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
                        name="username"
                        placeholder="Username"
                        value={credentials.username}
                        onChange={handleChange}
                        invalid={errorMsg !== ''}
                        autoComplete="username"
                        required
                        id="usernameinput"
                      />
                      {credentials.flag !== '0' && (
                        <>
                          <CInputGroupText>@</CInputGroupText>
                          <CFormInput
                            type="text"
                            name="domain"
                            placeholder="Domain"
                            value={credentials.domain}
                            onChange={handleChange}
                            invalid={errorMsg !== ''}
                            autoComplete="organization"
                            required
                            id="domainInput"
                          />
                        </>
                      )}
                    </CInputGroup>
                    <CInputGroup className={credentials.flag === '2' ? 'mb-2' : 'mb-4'}>
                      <CInputGroupText>
                        <CIcon icon={cilLockLocked} />
                      </CInputGroupText>
                      <CFormInput
                        type={showPassword ? 'text' : 'password'}
                        name="password"
                        placeholder="Password"
                        value={credentials.password}
                        onChange={handleChange}
                        invalid={errorMsg !== ''}
                        autoComplete="current-password"
                        required
                        id="passwordinput"
                      />
                      <CInputGroupText
                        onClick={togglePasswordVisibility}
                        style={{ cursor: 'pointer' }}
                      >
                        <img
                          src={showPassword ? EyeClosedIcon : EyeOpenIcon}
                          height={18}
                          width={18}
                        />
                      </CInputGroupText>
                    </CInputGroup>

                    {credentials.flag === '2' && (
                      <Login_User credentials={credentials} handleChange={handleChange} />
                    )}

                    <CRow>
                      <CCol xs={6}>
                        <CButton color="primary" className="px-4" type="submit">
                          Login
                        </CButton>
                      </CCol>
                      <CCol xs={6} className="text-end">
                        <CButton as="a" href="/password_reset" color="link" className="px-0">
                          Forgot password?
                        </CButton>
                      </CCol>
                    </CRow>
                  </CForm>
                </CCardBody>
              </CCard>
            </CCol>
            <CCol className="d-flex align-items-center justify-content-center">
              <CCard className="text-white bg-transparent py-5 border-0">
                <CCardBody className="text-center">
                  <div>
                    <CImage src={new_logo} height={60} />
                    <p className="mt-2">OMNICHANNEL</p>
                    {/* <p className="login-message">Calls and Messaging made easy</p> */}
                    <p className="login-message">Making Every Conversation Count</p>
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
