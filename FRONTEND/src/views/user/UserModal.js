import {
  CButton,
  CCol,
  CForm,
  CFormCheck,
  CFormInput,
  CFormLabel,
  CFormSelect,
  CModal,
  CModalBody,
  CModalFooter,
  CModalHeader,
  CModalTitle,
  CRow,
} from '@coreui/react'
import React, { useState } from 'react'

const UserModal = ({ visible, setVisible, type }) => {
  const [userId, setUserId] = useState('')
  const [password, setPassword] = useState('')
  const [fullName, setFullName] = useState('')
  const [userGroup, setUserGroup] = useState('')
  const [userType, setUserType] = useState('Agent')
  const [perlOnlyUser, setPerlOnlyUser] = useState(false)

  const handleSubmit = (event) => {
    event.preventDefault()
    // Handle form submission
    console.log({ userId, password, fullName, userGroup, userType, perlOnlyUser })
  }

  const renderContent = () => {
    if (type === 'create') {
      return (
        <>
          <CModalHeader>
            <CModalTitle id="LiveDemoExampleLabel">Agent Details</CModalTitle>
          </CModalHeader>
          <CModalBody>
            <CForm id="userForm" onSubmit={handleSubmit}>
              <div className="row">
                <CRow className="mb-3">
                  <CFormLabel htmlFor="perlOnlyUser" className="col-sm-2 col-form-label">Perl Only User</CFormLabel>
                  <CCol sm={10}>
                    <CFormCheck
                      id="perlOnlyUser"
                      label={<small>(This is applicable to agents, after enabling User ID and
                        Password accept only digits, and also the user never converts into admin type user.)</small>}
                      checked={perlOnlyUser}
                      onChange={(e) => setPerlOnlyUser(e.target.checked)}
                    />
                  </CCol>
                </CRow>
                <CRow className="mb-3">
                  <CFormLabel htmlFor="userId" className="col-sm-2 col-form-label">User ID&nbsp;<span style={{ color: 'red' }}>*</span></CFormLabel>
                  <CCol sm={10}>
                    <CFormInput type="text"
                      id="userId"
                      value={userId}
                      onChange={(e) => setUserId(e.target.value)}
                      feedbackValid="Looks Good!"
                      required
                    />
                  </CCol>
                </CRow>
                <CRow className="mb-3">
                  <CFormLabel htmlFor="password" className="col-sm-2 col-form-label">Password&nbsp;<span style={{ color: 'red' }}>*</span></CFormLabel>
                  <CCol sm={10}>
                    <CFormInput
                      type="password"
                      id="password"
                      feedbackInvalid="Please enter a valid password"
                      value={password}
                      onChange={(e) => setPassword(e.target.value)}
                      required
                    />
                  </CCol>
                </CRow>
                <CRow className="mb-3">
                  <CFormLabel htmlFor="fullName" className="col-sm-2 col-form-label">Full Name&nbsp;<span style={{ color: 'red' }}>*</span></CFormLabel>
                  <CCol sm={10}>
                    <CFormInput
                      type="text"
                      id="fullName"
                      value={fullName}
                      onChange={(e) => setFullName(e.target.value)}
                      required
                    />
                  </CCol>
                </CRow>
                <CRow className="mb-3">
                  <CFormLabel htmlFor="userGroup" className="col-sm-2 col-form-label">User Group&nbsp;<span style={{ color: 'red' }}>*</span></CFormLabel>
                  <CCol sm={10}>
                    <CFormSelect
                      id="userGroup"
                      value={userGroup}
                      onChange={(e) => setUserGroup(e.target.value)}
                      required
                    >
                      <option value="">--- Select User Group ---</option>
                      <option value="group1">Group 1</option>
                      <option value="group2">Group 2</option>
                    </CFormSelect>
                  </CCol>
                </CRow>
                <CRow className="mb-3">
                  <CFormLabel className="col-sm-2 col-form-label">User Type</CFormLabel>
                  <CCol sm={10}>
                    <div>
                      <CFormCheck
                        type="radio"
                        id="agent"
                        name="userType"
                        label="Agent"
                        value="Agent"
                        checked={userType === 'Agent'}
                        onChange={(e) => setUserType(e.target.value)}
                      />
                      <CFormCheck
                        type="radio"
                        id="admin"
                        name="userType"
                        label="Admin"
                        value="Admin"
                        checked={userType === 'Admin'}
                        onChange={(e) => setUserType(e.target.value)}
                      />
                    </div>
                  </CCol>
                </CRow>
              </div>
            </CForm>
          </CModalBody>
          <CModalFooter>
            <CButton type="submit" color="primary" form="userForm">Submit</CButton>
            <CButton color="secondary" onClick={() => setVisible(false)}>
              Close
            </CButton>
          </CModalFooter>
        </>
      )
    } else if (type === 'delete') {
      return (
        <>
          <CModalHeader>
            <CModalTitle id="LiveDemoExampleLabel">Delete User</CModalTitle>
          </CModalHeader>
          <CModalBody>
            <p>Are you sure you want to delete this user?</p>
          </CModalBody>
          <CModalFooter>
            <CButton color="secondary" onClick={() => setVisible(false)}>
              Cancel
            </CButton>
            <CButton color="danger">Delete</CButton>
          </CModalFooter>
        </>
      )
    }
  }

  return (
    <CModal
      size={type === 'create' ? 'lg' : ''}
      backdrop="static"
      visible={visible}
      onClose={() => setVisible(false)}
      aria-labelledby="LiveDemoExampleLabel"
      className={type === 'create' ? "responsive-modal" : ''}
    >
      {renderContent()}
    </CModal>
  )
}

export default UserModal
