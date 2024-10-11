import React from 'react'
// import { NavLink } from 'react-router-dom'
// import PropTypes from 'prop-types'

import SimpleBar from 'simplebar-react'
import 'simplebar-react/dist/simplebar.min.css'

import { CBadge, CButton, CCol, CContainer, CFormInput, CNavLink, CRow, CSidebarNav } from '@coreui/react'
import { useSelector } from 'react-redux'
import CIcon from '@coreui/icons-react'
import { cilHeader, cilMediaPause, cilMediaPlay, cilScrubber, cilStar, cilTransfer, cilX } from '@coreui/icons'

// import PhonePausedIcon from '@mui/icons-material/PhonePaused';

// export const AppSidebarNavAgent = ({ items }) => {
export const AppSidebarNavSuperAdmin = () => {
  // const navLink = (name, icon, badge, indent = false) => {
  //   return (
  //     <>
  //       {icon
  //         ? icon
  //         : indent && (
  //             <span className="nav-icon">
  //               <span className="nav-icon-bullet"></span>
  //             </span>
  //           )}
  //       {name && name}
  //       {badge && (
  //         <CBadge color={badge.color} className="ms-auto">
  //           {badge.text}
  //         </CBadge>
  //       )}
  //     </>
  //   )
  // }

  // const navItem = (item, index, indent = false) => {
  //   const { component, name, badge, icon, ...rest } = item
  //   const Component = component
  //   return (
  //     <Component as="div" key={index}>
  //       {rest.to || rest.href ? (
  //         <CNavLink {...(rest.to && { as: NavLink })} {...rest}>
  //           {navLink(name, icon, badge, indent)}
  //         </CNavLink>
  //       ) : (
  //         navLink(name, icon, badge, indent)
  //       )}
  //     </Component>
  //   )
  // }

  // const navGroup = (item, index) => {
  //   const { component, name, icon, items, to, ...rest } = item
  //   const Component = component
  //   return (
  //     <Component compact as="div" key={index} toggler={navLink(name, icon)} {...rest}>
  //       {item.items?.map((item, index) =>
  //         item.items ? navGroup(item, index) : navItem(item, index, true),
  //       )}
  //     </Component>
  //   )
  // }

  const unfoldable = useSelector((state) => state.sidebarUnfoldable);

  return (
    <CSidebarNav as={SimpleBar}>
      {/* {items &&
        items.map((item, index) => (item.items ? navGroup(item, index) : navItem(item, index)))} */}

      <CRow className='mb-4'>
        {(unfoldable === false || unfoldable === undefined) ? (
          <>
            <CBadge color="warning" className="mx-auto">NO LIVE CALLS</CBadge>
            <CBadge color="success" className="mx-auto" >x LIVE CALLS</CBadge>
          </>
        ) : (
          <>
            <CBadge color="warning" >0 LIVE</CBadge>
            <CBadge color="success" >x LIVE</CBadge>
          </>
        )}

      </CRow>
      <CContainer className="mx-auto">
        <CRow className="mb-2 mx-auto">
          <CCol className="mx-auto d-flex justify-content-center" xs={4} sm={4} md={3} xl={2}>
            <CButton><CIcon icon={cilX} size="xxl" />Hangup</CButton>
          </CCol>
          <CCol className="mx-auto d-flex justify-content-center" xs={4} sm={4} md={3} xl={2}>
            <CButton><CIcon icon={cilMediaPause} size="xxl" />Pause</CButton>
          </CCol>
          <CCol className="mx-auto d-flex justify-content-center" xs={4} sm={4} md={3} xl={2}>
            <CButton><CIcon icon={cilMediaPlay} size="xxl" />Resume</CButton>
          </CCol>
        </CRow>
        <CRow className="mb-2 mx-auto">
          <CCol className="mx-auto d-flex justify-content-center align-items-center">
            <CButton><CIcon icon={cilHeader} size="xxl" />Hold</CButton>
          </CCol>
          <CCol className="mx-auto d-flex justify-content-center align-items-center">
            <CButton><CIcon icon={cilTransfer} size="xxl" />Transfer</CButton>
          </CCol>
        </CRow>
        <CRow className="mb-2">
          <CCol className="mx-auto d-flex justify-content-center align-items-center" sm={4}>
            <CButton><CIcon icon={cilScrubber} size="xxl" />Record</CButton>
          </CCol>
        </CRow>
        <CRow className="mx-auto">
          <p className='text-center'>Phone Number</p>
          <CFormInput style={{ backgroundColor: '#d0e1f977', color: '#fff' }} placeholder="Enter Phone Number Here"></CFormInput>
        </CRow>
        <CRow className="mb-2">
          <CCol className="mx-auto d-flex justify-content-center align-items-center">
            <CButton>Dial Now</CButton>
          </CCol>
        </CRow>
      </CContainer>


    </CSidebarNav>
  )
}

// AppSidebarNavAgent.propTypes = {
//   items: PropTypes.arrayOf(PropTypes.any).isRequired,
// }
