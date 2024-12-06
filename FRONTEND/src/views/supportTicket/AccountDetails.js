import React from 'react'
import {
  COffcanvas,
  COffcanvasHeader,
  COffcanvasBody,
  CAccordion,
  CAccordionItem,
  CAccordionHeader,
  CAccordionBody,
  CCloseButton,
  CCol,
  CCard,
  CCardBody,
  CFormLabel,
} from '@coreui/react'

const AccountDetails = ({ onClose }) => {
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
          <h5>Account Details</h5>
        </CCol>
        <CCol xs="auto" className="ms-auto">
          <CCloseButton className="text-reset" onClick={onClose} />
        </CCol>
      </COffcanvasHeader>
      <COffcanvasBody>
        <CAccordion activeItemKey={1}>
          <CAccordionItem itemKey={1}>
            <CAccordionHeader>Accordion Item #1</CAccordionHeader>
            <CAccordionBody>
              <CCol xs="12" md="6" lg="4" className="mb-4">
                <CCard>
                  <CCardBody>
                    <CFormLabel>Test</CFormLabel>
                    <div>Value</div>
                  </CCardBody>
                </CCard>
              </CCol>
            </CAccordionBody>
          </CAccordionItem>
        </CAccordion>
      </COffcanvasBody>
    </COffcanvas>
  )
}

export default AccountDetails
