// import React from 'react'
// import {
//   COffcanvas,
//   COffcanvasHeader,
//   COffcanvasBody,
//   CAccordion,
//   CAccordionItem,
//   CAccordionHeader,
//   CAccordionBody,
//   CCloseButton,
//   CCol,
//   CCard,
//   CCardBody,
//   CFormLabel,
// } from '@coreui/react'

// const AccountDetails = ({ onClose }) => {
//   return (
//     <COffcanvas
//       visible={true}
//       placement="end"
//       onHide={onClose}
//       backdrop="static"
//       style={{ width: '60%' }}
//       scroll={true}
//     >
//       <COffcanvasHeader>
//         <CCol>
//           <h5>Account Details</h5>
//         </CCol>
//         <CCol xs="auto" className="ms-auto">
//           <CCloseButton className="text-reset" onClick={onClose} />
//         </CCol>
//       </COffcanvasHeader>
//       <COffcanvasBody>
//         <CAccordion activeItemKey={1}>
//           <CAccordionItem itemKey={1}>
//             <CAccordionHeader>Accordion Item #1</CAccordionHeader>
//             <CAccordionBody>
//               <CCol xs="12" md="6" lg="4" className="mb-4">
//                 <CCard>
//                   <CCardBody>
//                     <CFormLabel>Test</CFormLabel>
//                     <div>Value</div>
//                   </CCardBody>
//                 </CCard>
//               </CCol>
//             </CAccordionBody>
//           </CAccordionItem>
//         </CAccordion>
//       </COffcanvasBody>
//     </COffcanvas>
//   )
// }

// export default AccountDetails

import React, { useState, useEffect, useContext } from 'react'
import axios from 'axios'
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
  CRow,
} from '@coreui/react'
import { AuthContext } from '../../context/AuthContext'
import { formatDateWithTime } from '../../utils/DateUtils'

const AccountDetails = ({ onClose, accountId }) => {
  const { authToken } = useContext(AuthContext)
  const [accountData, setAccountData] = useState(null) // Store API data
  const [loading, setLoading] = useState(true) // Handle loading state
  const [error, setError] = useState(null) // Handle error state

  // Fetch data when component mounts or when accountId changes
  useEffect(() => {
    const fetchAccountDetails = async () => {
      try {
        setLoading(true)
        const response = await axios.get(`http://127.0.0.1:8000/api/accounts/${accountId}`, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        })
        setAccountData(response.data.data) // Save the "data" object from the response
      } catch (err) {
        setError(err.message || 'An error occurred')
      } finally {
        setLoading(false)
      }
    }

    fetchAccountDetails()
  }, [accountId])

  if (loading) {
    return <div>Loading...</div>
  }

  if (error) {
    return <div>Error: {error}</div>
  }

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
            <CAccordionHeader>General Information</CAccordionHeader>
            <CAccordionBody>
              <CRow>
                <CCol xs="12" md="6" lg="4" className="mb-4">
                  <CCard style={{ boxShadow: '2px 2px 5px 1px #0003' }}>
                    {/* Label - Smaller/Muted */}
                    <CCardBody style={{padding: '5px 10px'}}>
                      <CFormLabel className="text-muted" style={{ fontSize: '0.85rem' }}>
                        Name
                      </CFormLabel>
                      {/* Content - Larger */}
                      <div>{accountData.name || 'N/A'}</div>
                    </CCardBody>
                  </CCard>
                </CCol>
                <CCol xs="12" md="6" lg="4" className="mb-4">
                  <CCard style={{ boxShadow: '2px 2px 5px 1px #0003' }}>
                    <CCardBody style={{padding: '5px 10px'}}>
                      <CFormLabel className="text-muted" style={{ fontSize: '0.85rem' }}>
                        Email
                      </CFormLabel>
                      <div>{accountData.email || 'N/A'}</div>
                    </CCardBody>
                  </CCard>
                </CCol>
                <CCol xs="12" md="6" lg="4" className="mb-4">
                  <CCard style={{ boxShadow: '2px 2px 5px 1px #0003' }}>
                    <CCardBody style={{padding: '5px 10px'}}>
                      <CFormLabel className="text-muted" style={{ fontSize: '0.85rem' }}>
                        Mobile
                      </CFormLabel>
                      <div>{accountData.mobile || 'N/A'}</div>
                    </CCardBody>
                  </CCard>
                </CCol>
                <CCol xs="12" md="6" lg="4" className="mb-4">
                  <CCard style={{ boxShadow: '2px 2px 5px 1px #0003' }}>
                    <CCardBody style={{padding: '5px 10px'}}>
                      <CFormLabel className="text-muted" style={{ fontSize: '0.85rem' }}>
                        Address
                      </CFormLabel>
                      <div>{accountData.address || 'N/A'}</div>
                    </CCardBody>
                  </CCard>
                </CCol>
                <CCol xs="12" md="6" lg="4" className="mb-4">
                  <CCard style={{ boxShadow: '2px 2px 5px 1px #0003' }}>
                    <CCardBody style={{padding: '5px 10px'}}>
                      <CFormLabel className="text-muted" style={{ fontSize: '0.85rem' }}>
                        Pincode
                      </CFormLabel>
                      <div>{accountData.pincode || 'N/A'}</div>
                    </CCardBody>
                  </CCard>
                </CCol>
                <CCol xs="12" md="6" lg="4" className="mb-4">
                  <CCard style={{ boxShadow: '2px 2px 5px 1px #0003' }}>
                    <CCardBody style={{padding: '5px 10px'}}>
                      <CFormLabel className="text-muted" style={{ fontSize: '0.85rem' }}>
                        Description
                      </CFormLabel>
                      <div>{accountData.description || 'N/A'}</div>
                    </CCardBody>
                  </CCard>
                </CCol>
                <CCol xs="12" md="6" lg="4" className="mb-4">
                  <CCard style={{ boxShadow: '2px 2px 5px 1px #0003' }}>
                    <CCardBody style={{padding: '5px 10px'}}>
                      <CFormLabel className="text-muted" style={{ fontSize: '0.85rem' }}>
                        Company Name
                      </CFormLabel>
                      <div>{accountData.company_name || 'N/A'}</div>
                    </CCardBody>
                  </CCard>
                </CCol>
                <CCol xs="12" md="6" lg="4" className="mb-4">
                  <CCard style={{ boxShadow: '2px 2px 5px 1px #0003' }}>
                    <CCardBody style={{padding: '5px 10px'}}>
                      <CFormLabel className="text-muted" style={{ fontSize: '0.85rem' }}>
                        Contact Person
                      </CFormLabel>
                      <div>
                        {accountData.contact_person_first_name || 'N/A'}{' '}
                        {accountData.contact_person_last_name || ''}
                      </div>
                    </CCardBody>
                  </CCard>
                </CCol>
                <CCol xs="12" md="6" lg="4" className="mb-4">
                  <CCard style={{ boxShadow: '2px 2px 5px 1px #0003' }}>
                    <CCardBody style={{padding: '5px 10px'}}>
                      <CFormLabel className="text-muted" style={{ fontSize: '0.85rem' }}>
                        Contact Number
                      </CFormLabel>
                      <div>{accountData.contact_no || 'N/A'}</div>
                    </CCardBody>
                  </CCard>
                </CCol>
                <CCol xs="12" md="6" lg="4" className="mb-4">
                  <CCard style={{ boxShadow: '2px 2px 5px 1px #0003' }}>
                    <CCardBody style={{padding: '5px 10px'}}>
                      <CFormLabel className="text-muted" style={{ fontSize: '0.85rem' }}>
                        Created At
                      </CFormLabel>
                      <div>{formatDateWithTime(accountData.created_at) || 'N/A'}</div>
                    </CCardBody>
                  </CCard>
                </CCol>
              </CRow>
            </CAccordionBody>
          </CAccordionItem>
        </CAccordion>
      </COffcanvasBody>
    </COffcanvas>
  )
}

export default AccountDetails
