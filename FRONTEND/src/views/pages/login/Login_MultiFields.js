import React, { useState } from 'react'
import EyeOpenIcon from '../../../assets/images/icons/eye_open.svg'
import EyeClosedIcon from '../../../assets/images/icons/eye_closed.svg'
import axios from 'axios'
import {
  CButton,
  CCallout,
  CCard,
  CCardBody,
  CCol,
  CContainer,
  CForm,
  CFormFeedback,
  CFormInput,
  CFormSelect,
  CImage,
  CInputGroup,
  CInputGroupText,
  CRow,
} from '@coreui/react'
import CIcon from '@coreui/icons-react'
import { cilLockLocked, cilPhone, cilReload, cilUser } from '@coreui/icons'
import { useNavigate } from 'react-router-dom'
import { BASE_URL } from '../../../config'

const Login_MultiFields = () => {
  const [userName, setUserName] = useState('')
  const [password, setPassword] = useState('')

  const [errorMsg, setErrorMsg] = useState('')

  const [showPassword, setShowPassword] = useState(false);

  const togglePasswordVisibility = () => {
    setShowPassword(!showPassword);
  };

  const navigate = useNavigate() 
  const handleSubmit = async (e) => {
    e.preventDefault()

    setErrorMsg('')

    let TENANT_AUTH_USER = userName
    let TENANT_AUTH_PASS = password

    try {
      const response = await axios.post(
        `${BASE_URL}/tenant/api/login.php`,
        {
          TENANT_AUTH_USER,
          TENANT_AUTH_PASS,
        },
        {
          headers: {
            'Content-Type': 'application/json',

          },
 
        },
      )

      if (response.status == 200) {
        if (response.data.token) {
          localStorage.setItem('authToken', response.data.token)

          console.log(response.data.code + ' ' + response.data.message)
          navigate('/agent')
        } else {
          console.log('200 but some issue exists');
        }
      } else {
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
      }
      // }
    }
  }

  return (
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
                        {errorMsg} hello
                      </CCallout>
                    )}
                    <CRow>
                      <CCol>
                        <CInputGroup className="mb-2">
                          <CInputGroupText>
                            <CIcon icon={cilPhone} />
                          </CInputGroupText>
                          <CFormInput
                            size='sm'
                            type="text"
                            placeholder="Phone Login"
                            value={userName}
                            onChange={(e) => setUserName(e.target.value)}
                            invalid={errorMsg !== ''}
                            required
                          />
                        </CInputGroup>
                      </CCol>
                      <CCol>
                        <CInputGroup className="mb-2">
                          <CInputGroupText>
                            <CIcon icon={cilLockLocked} />
                          </CInputGroupText>
                          <CFormInput
                            size='sm'
                            type={showPassword ? 'text' : 'password'}
                            placeholder="Phone Password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            invalid={errorMsg !== ''}
                            autoComplete="current-password"
                            required
                          />
                          <CInputGroupText onClick={togglePasswordVisibility} style={{ cursor: 'pointer' }}>
                            <img src={showPassword ? EyeOpenIcon : EyeClosedIcon} height={18} width={18} />
                          </CInputGroupText>
                        </CInputGroup>
                      </CCol>
                    </CRow>
                    <CRow>
                      <CCol>
                        <CInputGroup className="mb-2">
                          <CInputGroupText>
                            <CIcon icon={cilUser} />
                          </CInputGroupText>
                          <CFormInput
                            size='sm'
                            type="text"
                            placeholder="User Login"
                            value={userName}
                            onChange={(e) => setUserName(e.target.value)}
                            invalid={errorMsg !== ''}
                            autoComplete="username"
                            required
                          />
                        </CInputGroup>
                      </CCol>
                      <CCol>
                        <CInputGroup className="mb-2">
                          <CInputGroupText>
                            <CIcon icon={cilLockLocked} />
                          </CInputGroupText>
                          <CFormInput
                            size='sm'
                            type="password"
                            placeholder="User Password"
                            value={password}
                            onChange={(e) => setPassword(e.target.value)}
                            invalid={errorMsg !== ''}
                            autoComplete="current-password"
                            required
                          />
                        </CInputGroup>
                      </CCol>
                    </CRow>
                    <CInputGroup className="mb-2">
                      <CFormSelect
                        size='sm'
                        id="userGroup"
                        // value={userGroup}
                        value="test"
                        // onChange={(e) => setUserGroup(e.target.value)}
                        
                      >
                        <option value="test">--- Select Campaign ---</option>
                        <option value="group1">Group 1</option>
                        <option value="group2">Group 2</option>
                      </CFormSelect>
                      <CInputGroupText style={{ cursor: 'pointer' }}>
                        <CIcon icon={cilReload} />
                      </CInputGroupText>
                    </CInputGroup>
                    <CFormSelect className="mb-2"
                      size='sm'
                      id="userGroup"
                      // value={userGroup}
                      value="test"
                      // onChange={(e) => setUserGroup(e.target.value)}
                      
                    >
                      <option value="test">--- Select Ingroup ---</option>
                      <option value="group1">Group 1</option>
                      <option value="group2">Group 2</option>
                    </CFormSelect>
                    <CInputGroup className="mb-4">
                      <CInputGroupText>
                        <CIcon icon={cilLockLocked} />
                      </CInputGroupText>
                      <CFormInput
                        size='sm'
                        type="text"
                        placeholder="URL"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                      />
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
            <CCol className='align-content-center'>
              <CCard className="text-white bg-transparent py-5 border-0">
                <CCardBody className="text-center">
                  <div>
                    <CImage src="src\assets\brand\new_logo.png" height={80} />
                    <p className="login-message">Calls and Messaging made easy</p>
                  </div>
                </CCardBody>
              </CCard>
            </CCol>
          </CRow>
        </CContainer>
      </div>
    </div >
  )
}

export default Login_MultiFields