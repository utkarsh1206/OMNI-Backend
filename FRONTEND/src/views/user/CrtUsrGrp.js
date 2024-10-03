import React, { useEffect, useState } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CCol,
  CForm,
  CFormInput,
  CFormLabel,
  CRow,
} from '@coreui/react'

const UserGroups = () => {
  const [group, setGroup] = useState('')
  const [description, setDescription] = useState('')

  const handleSubmit = (event) => {
    event.preventDefault()
    // Handle form submission
    console.log({ group, description })
  }

  return (
    <>
      <CRow>
        <CCol xs={12}>
          <CCard className="mb-4">
            <CCardHeader>
              <strong>User Group Details</strong>
            </CCardHeader>
            <CCardBody>
              <CForm id="userGroupForm" onSubmit={handleSubmit}>
                <div className="row">
                  <CRow className="mb-3">
                    <CFormLabel htmlFor="group" className="col-sm-2 col-form-label">
                      Group&nbsp;<span style={{ color: 'red' }}>*</span>
                    </CFormLabel>
                    <CCol sm={10}>
                      <CFormInput
                        type="text"
                        id="group"
                        text="(no spaces or punctuation)"
                        value={group}
                        onChange={(e) => setGroup(e.target.value)}
                        required
                      />
                    </CCol>
                  </CRow>
                  <CRow className="mb-3">
                    <CFormLabel htmlFor="description" className="col-sm-2 col-form-label">
                      Description&nbsp;<span style={{ color: 'red' }}>*</span>
                    </CFormLabel>
                    <CCol sm={10}>
                      <CFormInput
                        type="textarea"
                        id="description"
                        text="(description of group)"
                        value={description}
                        onChange={(e) => setDescription(e.target.value)}
                        required
                      />
                    </CCol>
                  </CRow>
                </div>
                <CButton type="submit" color="primary" form="userGroupForm">
                  Submit
                </CButton>
              </CForm>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>
    </>
  )
}

export default UserGroups
