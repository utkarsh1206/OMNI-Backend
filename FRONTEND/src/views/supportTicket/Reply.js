import React from 'react'
import {
  COffcanvas,
  COffcanvasHeader,
  COffcanvasBody,
  CCloseButton,
  CCol,
} from '@coreui/react'

const Reply = ({ onClose }) => {
  return (
    <COffcanvas
      visible={true}
      placement="end"
      onHide={onClose}
      backdrop="static"
      style={{ width: '60%' }}
      scroll={true}
    >
      <COffcanvasHeader>
        <CCol>
          <h5>Reply</h5>
        </CCol>
        <CCol xs="auto" className="ms-auto">
          <CCloseButton className="text-reset" onClick={onClose} />
        </CCol>
      </COffcanvasHeader>
      <COffcanvasBody>Reply Here</COffcanvasBody>
    </COffcanvas>
  )
}

export default Reply
