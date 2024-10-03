import React from 'react';
import {
  CCard,
  CCardBody,
  CCardHeader,
  CRow,
  CCol,
  CFormInput
} from '@coreui/react';
import './AgentDashboard.css'; // Import the CSS file

const AgentDashboard = () => {
  return (
    <div className="container">
      <CCard className="custom-card">
        <CCardHeader className="agent-dash-header">Agent Statistics</CCardHeader>
        <CCardBody>
          <CRow className="text-center">
            <CCol className="text-primary">
              <div>Agents Logged in</div>
              <div className="data-value1">1</div>
            </CCol>
            <CCol className="text-success">
              <div>Agents in Calls</div>
              <div className="data-value1">0</div>
            </CCol>
            <CCol className="text-danger">
              <div>Agents Waiting</div>
              <div className="data-value1">0</div>
            </CCol>
            <CCol className="text-warning">
              <div>Agents in Paused</div>
              <div className="data-value1">1</div>
            </CCol>
            <CCol className="text-dark">
              <div>Dead Calls</div>
              <div className="data-value1">0</div>
            </CCol>
            <CCol className="text-warning">
              <div>Agents in Lagged</div>
              <div className="data-value1">0</div>
            </CCol>
            <CCol className="text-info">
              <div>Agents in Dispos</div>
              <div className="data-value1">0</div>
            </CCol>
          </CRow>
        </CCardBody>
      </CCard>
    </div>
  );
}

export default AgentDashboard;
