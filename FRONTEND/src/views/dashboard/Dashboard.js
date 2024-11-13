import React from 'react';
import {
  CCard,
  CCardBody,
  CCol,
  CRow,
  CButton,
  CWidgetStatsA,
  CTable,
  CTableHead,
  CTableBody,
  CTableRow,
  CTableHeaderCell,
  CTableDataCell,
  CBadge
} from '@coreui/react';

const Dashboard = () => {
  return (
    <div className="dashboard">
      <CRow className="mb-4">
        <CCol>
          <h3>Dashboard</h3>
        </CCol>
        <CCol className="text-end">
          <CButton color="warning">Add Ticket</CButton>
        </CCol>
      </CRow>

      <CRow className="mb-4">
        <CCol xs="2">
          <CWidgetStatsA
            color="warning"
            value={<h2>1</h2>}
            title="Unassigned"
          />
        </CCol>
        <CCol xs="2">
          <CWidgetStatsA
            color="primary"
            value={<h2>0</h2>}
            title="Open"
          />
        </CCol>
        <CCol xs="2">
          <CWidgetStatsA
            color="info"
            value={<h2>0</h2>}
            title="Waiting On Customer"
          />
        </CCol>
        <CCol xs="2">
          <CWidgetStatsA
            color="success"
            value={<h2>0</h2>}
            title="Waiting On Third Party"
          />
        </CCol>
        <CCol xs="2">
          <CWidgetStatsA
            color="secondary"
            value={<h2>0</h2>}
            title="Resolved"
          />
        </CCol>
        <CCol xs="2">
          <CWidgetStatsA
            color="dark"
            value={<h2>0</h2>}
            title="Closed"
          />
        </CCol>
      </CRow>

      <CRow>
        <CCol xs="6">
          <CCard>
            <CCardBody>
              <h5>Type Wise Tickets</h5>
              <CTable>
                <CTableHead>
                  <CTableRow>
                    <CTableHeaderCell>#</CTableHeaderCell>
                    <CTableHeaderCell>Type</CTableHeaderCell>
                    <CTableHeaderCell>Count</CTableHeaderCell>
                  </CTableRow>
                </CTableHead>
                <CTableBody>
                  {[
                    { type: 'Problems', count: 1 },
                    { type: 'Help', count: 0 },
                    { type: 'Incident', count: 0 },
                    { type: 'Questions', count: 0 },
                    { type: 'Feature Request', count: 0 },
                    { type: 'Other', count: 0 },
                  ].map((item, index) => (
                    <CTableRow key={index}>
                      <CTableDataCell>{index + 1}</CTableDataCell>
                      <CTableDataCell>{item.type}</CTableDataCell>
                      <CTableDataCell>{item.count}</CTableDataCell>
                    </CTableRow>
                  ))}
                </CTableBody>
              </CTable>
            </CCardBody>
          </CCard>
        </CCol>

        <CCol xs="6">
          <CCard>
            <CCardBody>
              <h5>Priority Wise Tickets</h5>
              <CTable>
                <CTableHead>
                  <CTableRow>
                    <CTableHeaderCell>#</CTableHeaderCell>
                    <CTableHeaderCell>Priority</CTableHeaderCell>
                    <CTableHeaderCell>Count</CTableHeaderCell>
                  </CTableRow>
                </CTableHead>
                <CTableBody>
                  {[
                    { priority: 'Medium', count: 0 },
                    { priority: 'High', count: 1 },
                    { priority: 'Low', count: 0 },
                    { priority: 'Urgent', count: 0 },
                  ].map((item, index) => (
                    <CTableRow key={index}>
                      <CTableDataCell>{index + 1}</CTableDataCell>
                      <CTableDataCell>{item.priority}</CTableDataCell>
                      <CTableDataCell>{item.count}</CTableDataCell>
                    </CTableRow>
                  ))}
                </CTableBody>
              </CTable>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      <CRow className="mt-4">
        <CCol xs="12">
          <CCard>
            <CCardBody>
              <h5>Today's Stats</h5>
              <div className="d-flex justify-content-between mb-2">
                <CBadge color="danger">Pause: 12</CBadge>
                <CBadge color="success">Resume: 0</CBadge>
              </div>
              <CTable>
                <CTableHead>
                  <CTableRow>
                    <CTableHeaderCell>Name</CTableHeaderCell>
                    <CTableHeaderCell>Current State</CTableHeaderCell>
                    <CTableHeaderCell>Agent Status</CTableHeaderCell>
                  </CTableRow>
                </CTableHead>
                <CTableBody>
                  {[
                    { name: 'Utkarsh Rokade', id: '7003', time: '10:12AM', status: 'Pause' },
                    { name: 'Harsh Patel', id: '7005', time: '10:09AM', status: 'Active' },
                    { name: 'Gunjan Poshiya', id: '7007', time: '10:25AM', status: 'Pause' },
                    { name: 'Laxman Sonwane', id: '7008', time: '10:15AM', status: 'Pause' },
                  ].map((agent, index) => (
                    <CTableRow key={index}>
                      <CTableDataCell>{agent.name} {agent.id}</CTableDataCell>
                      <CTableDataCell>
                        <CBadge color="success">Login</CBadge> [{agent.time}]
                      </CTableDataCell>
                      <CTableDataCell>
                        <CBadge color={agent.status == 'Pause' ? "danger" :"success"}>{agent.status}</CBadge>
                      </CTableDataCell>
                    </CTableRow>
                  ))}
                </CTableBody>
              </CTable>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>
    </div>
  );
};

export default Dashboard;
