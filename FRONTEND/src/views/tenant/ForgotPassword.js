import React, { useContext, useState } from 'react'
import axios from 'axios'
import {
  CButton,
  CCallout,
  CCard,
  CCardBody,
  CCol,
  CForm,
  CFormInput,
  CInputGroup,
  CInputGroupText,
  CRow,
} from '@coreui/react'
import CIcon from '@coreui/icons-react'
import EyeOpenIcon from '../../assets/images/icons/eye_open.svg'
import EyeClosedIcon from '../../assets/images/icons/eye_closed.svg'
import { cilLockLocked } from '@coreui/icons'
import { useNavigate } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'

const ForgotPassword = () => {
  const { authToken } = useContext(AuthContext)
  const navigate = useNavigate()

  const [credentials, setCredentials] = useState({
    old_password: '',
    new_password: '',
    new_password_confirmation: '',
  })

  // Separate visibility states for each password field
  const [showPassword, setShowPassword] = useState({
    old_password: false,
    new_password: false,
    new_password_confirmation: false,
  })

  const togglePasswordVisibility = (field) => {
    setShowPassword((prevState) => ({
      ...prevState,
      [field]: !prevState[field],
    }))
  }

  const [errorMsg, setErrorMsg] = useState('')

  const handleChange = (e) => {
    setCredentials({ ...credentials, [e.target.name]: e.target.value })
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setErrorMsg('')
    try {
      axios
        .put(`${BASE_URL}/api/update-tenantpassword`, credentials, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        .then(() => {
          alert('Password Changed Succesfully')
          navigate('/dashboard')
        })
        .catch((error) => {
          console.error('Error Changing Password: ', error)
          alert('Failed to Change Password.')
        })
    } catch (err) {
      setErrorMsg('Invalid')
    }
  }

  return (
    <CCard lg={6} className="p-4">
      <CCardBody>
        <CForm onSubmit={handleSubmit}>
          <h1>Change Password!</h1>
          <p className="text-body-secondary">Please enter your current and new password.</p>
          {errorMsg && (
            <CCallout color="danger" style={{ color: '#e55353' }}>
              {errorMsg}
            </CCallout>
          )}

          <CInputGroup className="mb-2">
            <CInputGroupText>
              <CIcon icon={cilLockLocked} />
            </CInputGroupText>
            <CFormInput
              type={showPassword.old_password ? 'text' : 'password'}
              name="old_password"
              placeholder="Old Password"
              value={credentials.old_password}
              onChange={handleChange}
              invalid={errorMsg !== ''}
              autoComplete="old_password"
              required
              id="old_passwordinput"
            />
            <CInputGroupText
              onClick={() => togglePasswordVisibility('old_password')}
              style={{ cursor: 'pointer' }}
            >
              <img
                src={showPassword.old_password ? EyeClosedIcon : EyeOpenIcon}
                height={18}
                width={18}
              />
            </CInputGroupText>
          </CInputGroup>

          <CInputGroup className="mb-2">
            <CInputGroupText>
              <CIcon icon={cilLockLocked} />
            </CInputGroupText>
            <CFormInput
              type={showPassword.new_password ? 'text' : 'password'}
              name="new_password"
              placeholder="New Password"
              value={credentials.new_password}
              onChange={handleChange}
              invalid={errorMsg !== ''}
              autoComplete="new_password"
              required
              id="new_passwordinput"
            />
            <CInputGroupText
              onClick={() => togglePasswordVisibility('new_password')}
              style={{ cursor: 'pointer' }}
            >
              <img
                src={showPassword.new_password ? EyeClosedIcon : EyeOpenIcon}
                height={18}
                width={18}
              />
            </CInputGroupText>
          </CInputGroup>

          <CInputGroup className="mb-2">
            <CInputGroupText>
              <CIcon icon={cilLockLocked} />
            </CInputGroupText>
            <CFormInput
              type={showPassword.new_password_confirmation ? 'text' : 'password'}
              name="new_password_confirmation"
              placeholder="Confirm Password"
              value={credentials.new_password_confirmation}
              onChange={handleChange}
              invalid={errorMsg !== ''}
              autoComplete="new_password_confirmation"
              required
              id="new_password_confirmation_input"
            />
            <CInputGroupText
              onClick={() => togglePasswordVisibility('new_password_confirmation')}
              style={{ cursor: 'pointer' }}
            >
              <img
                src={showPassword.new_password_confirmation ? EyeClosedIcon : EyeOpenIcon}
                height={18}
                width={18}
              />
            </CInputGroupText>
          </CInputGroup>
          <CRow>
            <CCol xs={6}>
              <CButton color="primary" className="px-4" type="submit">
                Update
              </CButton>
            </CCol>
          </CRow>
        </CForm>
      </CCardBody>
    </CCard>
  )
}

export default ForgotPassword
