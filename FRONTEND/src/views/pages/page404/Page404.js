import React from 'react'
import {
  CButton,
  CCol,
  CContainer,
  CFormInput,
  CInputGroup,
  CInputGroupText,
  CRow,
} from '@coreui/react'
import CIcon from '@coreui/icons-react'
import { cilMagnifyingGlass } from '@coreui/icons'
import { Link } from 'react-router-dom'

const Page404 = ({ redirectToPage }) => {
  if (redirectToPage) {
    window.history.replaceState(null, '', '/404');
    window.location.href = '/404';
    return
  }
  return (
    <>
      <div className="m-3">
        <CButton color="primary" variant="outline" className="me-2" size="sm" onClick={() => window.history.back()}>
          {"< Back"}
        </CButton>
        <Link to="/dashboard">
          <CButton color="primary" variant="outline" className="me-2" size="sm">
            {"< Back To Home"}
          </CButton>
        </Link>
      </div>
      <div className="bg-body-tertiary d-flex flex-row align-items-center" style={{
        minHeight: "75vh"
      }}>
        <CContainer>
          <CRow className="justify-content-center">
            <CCol md={6}>
              <div className="clearfix">
                <h1 className="float-start display-3 me-4">404</h1>
                <h4 className="pt-3">Oops! You{"'"}re lost.</h4>
                <p className="text-body-secondary float-start">
                  The page you are looking for was not found.
                </p>
              </div>
              <CInputGroup className="input-prepend">
                <CInputGroupText>
                  <CIcon icon={cilMagnifyingGlass} />
                </CInputGroupText>
                <CFormInput type="text" placeholder="What are you looking for?" />
                <CButton color="info">Search</CButton>
              </CInputGroup>
            </CCol>
          </CRow>
        </CContainer>
      </div>
    </>
  )
}

export default Page404
