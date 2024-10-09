import React, { useState } from 'react'
import EyeOpenIcon from '../../../assets/images/icons/eye_open.svg'
import EyeClosedIcon from '../../../assets/images/icons/eye_closed.svg'
import {
  CCol,
  CFormInput,
  CInputGroup,
  CInputGroupText,
  CRow,
} from '@coreui/react'
import CIcon from '@coreui/icons-react'
import { cilLockLocked, cilPhone, cilReload, cilUser } from '@coreui/icons'

const Login_User = ({ credentials, handleChange }) => {

  const [showPassword, setShowPassword] = useState(false);

  const togglePasswordVisibility = () => {
    setShowPassword(!showPassword);
  };

  return (
    <>
      <CRow>
        <CCol>
          <CInputGroup className="mb-4">
            <CInputGroupText>
              <CIcon icon={cilPhone} />
            </CInputGroupText>
            <CFormInput
              type="text"
              name='phone'
              placeholder="Phone Login"
              value={credentials.phone}
              onChange={handleChange}
              required
            />
          </CInputGroup>
        </CCol>
        <CCol>
          <CInputGroup className="mb-4">
            <CInputGroupText>
              <CIcon icon={cilLockLocked} />
            </CInputGroupText>
            <CFormInput
              // size='sm'
              type={showPassword ? 'text' : 'password'}
              name='phone_password'
              placeholder="Phone Password"
              value={credentials.phone_password}
              onChange={handleChange}
              autoComplete="current-password"
              required
            />
            <CInputGroupText onClick={togglePasswordVisibility} style={{ cursor: 'pointer' }}>
              <img src={showPassword ? EyeClosedIcon : EyeOpenIcon} height={18} width={18} />
            </CInputGroupText>
          </CInputGroup>
        </CCol>
      </CRow>
      {/* <CRow>
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
              // invalid={errorMsg !== ''}
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
              // invalid={errorMsg !== ''}
              autoComplete="current-password"
              required
            />
          </CInputGroup>
        </CCol>
      </CRow> */}
      {/* <CInputGroup className="mb-2">
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
      </CFormSelect> */}
      {/* <CInputGroup className="mb-4">
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
      </CInputGroup> */}
    </>

  )
}

export default Login_User