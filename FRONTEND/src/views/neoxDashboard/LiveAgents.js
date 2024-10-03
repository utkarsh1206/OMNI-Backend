import React from 'react';
import {
  CCard,
  CCardBody,
  CCardHeader,
  CRow,
  CCol,
  CContainer,
  CTable,
  CTableHead,
  CTableBody,
  CTableRow,
  CTableHeaderCell,
  CTableDataCell
} from '@coreui/react';
import './AgentDashboard.css'; // Import the CSS file
import { TEXT_GREEN, TEXT_RED } from '../../config';

const LiveAgentsDashboard = () => {

  const cardStyle = {
    'backgrounColor': '#ffffff',
    'border': 'none',
    'boxShadow': '0 0 8px rgba(0, 0, 0, 0.2)',
    'borderRadius': '8px',
    'marginBottom': '5px',
  }

  const cardHeaderStyle = {
    'textAlign': 'center',
    'fontWeight': 'bold',
    'backgroundColor': '#d2dae1',
    'borderBottom': 'none',
    'borderRadius': '8px 8px 0 0',
    'padding': '10px 0',
  }

  const dataHeadStyle = {
    'fontSize': '18px',
  }

  const dataValueStyle = {
    'fontSize': '40px',
  }

  const textGreenStyle = {
    'color': TEXT_GREEN,
  }

  const textRedStyle = {
    'color': TEXT_RED,
  }

  const HasData = true;

  return (
    <>
      <div className="container">
        <CCard className="custom-card">
          <CCardHeader className="custom-header">Agent Statistics</CCardHeader>
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
      <CContainer>
        <CCard style={cardStyle}>
          <CCardHeader style={cardHeaderStyle}>
            Live Agents
          </CCardHeader>
          <CCardBody>
            <CTable responsive striped hover className='text-center'>
              <CTableHead color='info'>
                <CTableRow>
                  <CTableHeaderCell scope="col">User</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Extension Status</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Customer</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Campaign</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Login Ingroup</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Status Duration</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Calls</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Login Hr</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Ready Hr</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Talk Hr</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Talk Avg</CTableHeaderCell>
                  <CTableHeaderCell scope="col">In-Group</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Lagged Duration</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Rank</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Agent Logout</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                {(HasData == false) ? (
                  <>
                    <CTableRow>
                      <CTableDataCell colSpan={15}><strong>No Live Calls</strong></CTableDataCell>
                    </CTableRow>
                  </>
                ) : (
                  <>
                    <CTableRow>
                      <CTableHeaderCell scope="row">User1</CTableHeaderCell>
                      <CTableDataCell>Status1</CTableDataCell>
                      <CTableDataCell>Customer</CTableDataCell>
                      <CTableDataCell>Campaign1</CTableDataCell>
                      <CTableDataCell>LoginIngroup1</CTableDataCell>
                      <CTableDataCell>StatusDuration1</CTableDataCell>
                      <CTableDataCell>Calls1</CTableDataCell>
                      <CTableDataCell>LoginHr1</CTableDataCell>
                      <CTableDataCell>ReadyHr1</CTableDataCell>
                      <CTableDataCell>TalkHr1</CTableDataCell>
                      <CTableDataCell>TalkAvg1</CTableDataCell>
                      <CTableDataCell>InGroup1</CTableDataCell>
                      <CTableDataCell>LaggedDuration1</CTableDataCell>
                      <CTableDataCell>Rank1</CTableDataCell>
                      <CTableDataCell>AgentLogout1</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">User2</CTableHeaderCell>
                      <CTableDataCell>Status2</CTableDataCell>
                      <CTableDataCell>Customer</CTableDataCell>
                      <CTableDataCell>Campaign2</CTableDataCell>
                      <CTableDataCell>LoginIngroup2</CTableDataCell>
                      <CTableDataCell>StatusDuration2</CTableDataCell>
                      <CTableDataCell>Calls2</CTableDataCell>
                      <CTableDataCell>LoginHr2</CTableDataCell>
                      <CTableDataCell>ReadyHr2</CTableDataCell>
                      <CTableDataCell>TalkHr2</CTableDataCell>
                      <CTableDataCell>TalkAvg2</CTableDataCell>
                      <CTableDataCell>InGroup2</CTableDataCell>
                      <CTableDataCell>LaggedDuration2</CTableDataCell>
                      <CTableDataCell>Rank2</CTableDataCell>
                      <CTableDataCell>AgentLogout2</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">User3</CTableHeaderCell>
                      <CTableDataCell>Status3</CTableDataCell>
                      <CTableDataCell>Customer</CTableDataCell>
                      <CTableDataCell>Campaign3</CTableDataCell>
                      <CTableDataCell>LoginIngroup3</CTableDataCell>
                      <CTableDataCell>StatusDuration3</CTableDataCell>
                      <CTableDataCell>Calls3</CTableDataCell>
                      <CTableDataCell>LoginHr3</CTableDataCell>
                      <CTableDataCell>ReadyHr3</CTableDataCell>
                      <CTableDataCell>TalkHr3</CTableDataCell>
                      <CTableDataCell>TalkAvg3</CTableDataCell>
                      <CTableDataCell>InGroup3</CTableDataCell>
                      <CTableDataCell>LaggedDuration3</CTableDataCell>
                      <CTableDataCell>Rank3</CTableDataCell>
                      <CTableDataCell>AgentLogout3</CTableDataCell>
                    </CTableRow>
                  </>
                )
                }
              </CTableBody>
            </CTable>
          </CCardBody>
        </CCard>
      </CContainer>
    </>
  );
}

export default LiveAgentsDashboard;