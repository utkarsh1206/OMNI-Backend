import React, { useContext, useState } from 'react'

import axios from 'axios'
import { CCard, CCardBody, CCardHeader, CCardTitle, CCol, CRow } from '@coreui/react'
import CIcon from '@coreui/icons-react'
import { cilAt, cilClipboard, cilClock, cilEnvelopeOpen } from '@coreui/icons'
import { AuthContext } from '../../context/AuthContext'
import { Link, useNavigate } from 'react-router-dom'

const SettingsOptions = () => {
  const { authToken } = useContext(AuthContext)
  const navigate = useNavigate()

  return (
    <CCard className="mb-3">
      <CCardHeader>
        <CCardTitle>Choose Settings</CCardTitle>
      </CCardHeader>
      <CCardBody>
        <CRow>
          <CCol className="mb-3" md={4}>
            {/* <CCard>
              <CCardBody
                className="d-flex justify-content-center align-items-center"
                style={{ cursor: 'pointer' }}
                onClick={() => navigate(`/email_config_settings`)}
              >
                <CRow>
                  <CCol xs={3}>
                    <CIcon size="xxl" icon={cilAt} className="mr-2" />
                  </CCol>
                  <CCol>
                    <div>Email Settings</div>
                  </CCol>
                </CRow>
              </CCardBody>
            </CCard> */}
            <CCard>
              <CCardBody
                className="d-flex justify-content-start align-items-center"
                style={{ cursor: 'pointer', padding: '1rem' }}
                // onClick={() => navigate(`/settings/email_config_settings`)}
                onClick={() => navigate(`/settings/email_options`)}
              >
                <span className="d-flex align-items-center">
                  <CIcon size="xxl" icon={cilAt} className="me-3" />
                  <span>Email Settings</span>
                </span>
              </CCardBody>
            </CCard>
          </CCol>

          <CCol className="mb-3" md={4}>
            <CCard>
              <CCardBody
                className="d-flex justify-content-start align-items-center"
                style={{ cursor: 'pointer', padding: '1rem' }}
                // onClick={() => navigate(`/settings/email_config_settings`)}
                onClick={() => navigate(`/settings/email_templates`)}
              >
                <span className="d-flex align-items-center">
                  <CIcon size="xxl" icon={cilEnvelopeOpen} className="me-3" />
                  <span>Email Templates</span>
                </span>
              </CCardBody>
            </CCard>
          </CCol>

          <CCol className="mb-3" md={4}>
            <CCard>
              <CCardBody
                className="d-flex justify-content-start align-items-center"
                style={{ cursor: 'pointer', padding: '1rem' }}
                // onClick={() => navigate(`/settings/email_config_settings`)}
                onClick={() => navigate(`/settings/SLA_config_list`)}
              >
                <span className="d-flex align-items-center">
                  <CIcon size="xxl" icon={cilClipboard} className="me-3" />
                  <span>SLA Configuration</span>
                </span>
              </CCardBody>
            </CCard>
          </CCol>

          <CCol className="mb-3" md={4}>
            <CCard>
              <CCardBody
                className="d-flex justify-content-start align-items-center"
                style={{ cursor: 'pointer', padding: '1rem' }}
                // onClick={() => navigate(`/settings/email_config_settings`)}
                onClick={() => navigate(`/settings/CRON_configs`)}
              >
                <span className="d-flex align-items-center">
                  <CIcon size="xxl" icon={cilClock} className="me-3" />
                  <span>CRON Configuration</span>
                </span>
              </CCardBody>
            </CCard>
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>
  )
}

export default SettingsOptions
