import React, { useState } from 'react'
import { Link } from 'react-router-dom'
import {
  CButton,
  CCard,
  CCardBody,
  CCardGroup,
  CCol,
  CContainer,
  CForm,
  CFormFeedback,
  CFormInput,
  CImage,
  CInputGroup,
  CInputGroupText,
  CRow,
} from '@coreui/react'
import CIcon from '@coreui/icons-react'
import { cilLockLocked, cilUser } from '@coreui/icons'

const Login = () => {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [emailError, setEmailError] = useState('')
  const [passwordError, setPasswordError] = useState('')

  // const validateEmail = () => {
  //   if (!email) {
  //     setEmailError('Email is required')
  //     return false
  //   } else if (!/\S+@\S+\.\S+/.test(email)) {
  //     setEmailError('Invalid email address')
  //     return false
  //   } else {
  //     setEmailError('')
  //     return true
  //   }
  // }

  // const validatePassword = () => {
  //   if (!password) {
  //     setPasswordError('Password is required')
  //     return false
  //   } else if (password.length < 5) {
  //     setPasswordError('Password must be at least 5 characters long')
  //     return false
  //   } else if (!/(?=.*[A-Z])(?=.*[!@#$&*])(?=.*[0-9])/.test(password)) {
  //     setPasswordError(
  //       'Password must contain at least one uppercase letter, one special character, and one number',
  //     )
  //     return false
  //   } else {
  //     setPasswordError('')
  //     return true
  //   }
  // }

  const handleSubmit = async (e) => {
    e.preventDefault()

    // const isEmailValid = validateEmail()
    // const isPasswordValid = validatePassword()

    // if (isEmailValid && isPasswordValid) {
    // Handle form submission logic here (e.g., API call)

    let TENANT_AUTH_USER = email
    let TENANT_AUTH_PASS = password

    try {
      const response = await fetch('http://192.168.0.118:9090/tenant/api/login.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Access-Control-Allow-Origin': '*',
          'Access-Control-Allow-Methods': 'GET,HEAD,OPTIONS,POST,PUT',
          'Access-Control-Allow-Headers': 'Origin, Content-Type',
        },
        body: JSON.stringify({
          TENANT_AUTH_USER,
          TENANT_AUTH_PASS,
        }),
        // credentials: 'include' // if you are using cookies
      })

      if (response.ok) {
        const data = await response.json()
        // Store the user's authentication token in local storage.
        localStorage.setItem('authToken', data.token)

        // Redirect the user to the home page.
        // window.location.href = '/';
        console.log('Successfully Logged In')
        alert(`Email: ${email}\nPassword: ${password}`)
      } else {
        // Handle the error.
        console.log('Some Error Occured')
        
      }
    } catch (error) {
      console.error('Error:', error)
    }
    // }
  }

  return (
    <div className="custom-backdrop">
      <div className="min-vh-100 d-flex flex-row align-items-center">
        <CContainer>
          {/* <CRow className="justify-content-left"> */}
          <CRow className="justify-content-between">
            {/* <CCol md={8}> */}
            <CCol>
              {/* <CCardGroup> */}
              <CCard lg={6} className="p-4">
                <CCardBody>
                  <CForm onSubmit={handleSubmit}>
                    <h1>Welcome!</h1>
                    <p className="text-body-secondary">Login to get started</p>
                    <CInputGroup className="mb-3">
                      <CInputGroupText>
                        <CIcon icon={cilUser} />
                      </CInputGroupText>
                      <CFormInput
                        // type="email"
                        type="text"
                        placeholder="Enter your Email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        // invalid={emailError !== ''}
                        autoComplete="username"
                        required
                      />
                      {/* {emailError && <CFormFeedback invalid>{emailError}</CFormFeedback>} */}
                    </CInputGroup>
                    <CInputGroup className="mb-4">
                      <CInputGroupText>
                        <CIcon icon={cilLockLocked} />
                      </CInputGroupText>
                      <CFormInput
                        type="password"
                        placeholder="Password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        // invalid={passwordError !== ''}
                        autoComplete="current-password"
                        required
                      />
                      {/* {passwordError && <CFormFeedback invalid>{passwordError}</CFormFeedback>} */}
                    </CInputGroup>
                    <CRow>
                      <CCol xs={6}>
                        <CButton color="primary" className="px-4" type="submit">
                          Login
                        </CButton>
                      </CCol>
                      <CCol xs={6} className="text-right">
                        {/* <CButton color="link" className="px-0">
                            Forgot password?
                          </CButton> */}
                      </CCol>
                    </CRow>
                  </CForm>
                </CCardBody>
              </CCard>
              {/* <CCard className="text-white bg-transparent py-5" style={{ width: '44%' }}> */}
            </CCol>
            <CCol>
              <CCard lg={6} className="text-white bg-transparent py-5 border-0">
                <CCardBody className="text-center">
                  <div>
                    <CImage src="src\assets\brand\new_logo.png" height={60} />
                    <p className="login-message">Calls and Messaging made easy</p>
                  </div>
                </CCardBody>
              </CCard>
              {/* </CCardGroup> */}
            </CCol>
          </CRow>
        </CContainer>
      </div>
    </div>
  )
}

export default Login
