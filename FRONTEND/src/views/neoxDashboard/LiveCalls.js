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
import './Dashboard.css'; // Import the CSS file
import { TEXT_GREEN, TEXT_RED } from '../../config';

const LiveCallsDashboard = () => {

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
      <CContainer>
        <CRow>
          <CCol sm="4">
            <CCard style={cardStyle}>
              <CCardHeader style={cardHeaderStyle}>Outbound Call Status</CCardHeader>
              <CCardBody>
                <CRow className="text-center">
                  <CCol className="text-green">
                    <div style={dataHeadStyle}>In Progress</div>
                    <div style={dataValueStyle}>0</div>
                  </CCol>
                  <CCol className="text-red">
                    <div style={dataHeadStyle}>Calls In Queue</div>
                    <div style={dataValueStyle}>0</div>
                  </CCol>
                </CRow>
              </CCardBody>
            </CCard>
          </CCol>
          <CCol sm="4">
            <CCard style={cardStyle}>
              <CCardHeader style={cardHeaderStyle}>System Total Call Status</CCardHeader>
              <CCardBody>
                <CRow className="text-center">
                  {/* <CCol style={textGreenStyle}> */}
                  <CCol className="text-green">
                    <div style={dataHeadStyle}>In Progress</div>
                    <div style={dataValueStyle}>0</div>
                  </CCol>
                  <CCol className="text-red">
                    <div style={dataHeadStyle}>Calls In Queue</div>
                    <div style={dataValueStyle}>0</div>
                  </CCol>
                </CRow>
              </CCardBody>
            </CCard>
          </CCol>
          <CCol sm="4">
            <CCard style={cardStyle}>
              <CCardHeader style={cardHeaderStyle}>Inbound Call Status</CCardHeader>
              <CCardBody>
                <CRow className="text-center">
                  <CCol className="text-green">
                    <div style={dataHeadStyle}>In Progress</div>
                    <div style={dataValueStyle}>0</div>
                  </CCol>
                  <CCol className="text-red">
                    <div style={dataHeadStyle}>Calls In Queue</div>
                    <div style={dataValueStyle}>0</div>
                  </CCol>
                </CRow>
              </CCardBody>
            </CCard>
          </CCol>
        </CRow>
      </CContainer>
      <CContainer>
        <CCard style={cardStyle}>
          <CCardHeader style={cardHeaderStyle}>
            Live Calls
          </CCardHeader>
          <CCardBody>
            <CTable striped hover className='text-center'>
              <CTableHead color='info'>
                <CTableRow>
                  <CTableHeaderCell scope="col">Customer</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Status</CTableHeaderCell>
                  <CTableHeaderCell scope="col">User</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Duration</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Ingroups</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Queue Position</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Delete</CTableHeaderCell>
                  <CTableHeaderCell scope="col">Disconnect and Logout</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                {(HasData == false) ? (
                  <>
                    <CTableRow>
                      <CTableDataCell colSpan={8}><strong>No Live Calls</strong></CTableDataCell>
                    </CTableRow>
                  </>
                ) : (
                  <>
                    <CTableRow>
                      <CTableHeaderCell scope="row">1</CTableHeaderCell>
                      <CTableDataCell>Mark</CTableDataCell>
                      <CTableDataCell>Otto</CTableDataCell>
                      <CTableDataCell>@mdo</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">3</CTableHeaderCell>
                      <CTableDataCell colSpan={2}>Larry the Bird</CTableDataCell>
                      <CTableDataCell>@twitter</CTableDataCell>
                      <CTableDataCell>Ingroup</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
                    </CTableRow>
                    <CTableRow>
                      <CTableHeaderCell scope="row">2</CTableHeaderCell>
                      <CTableDataCell>Jacob</CTableDataCell>
                      <CTableDataCell>Thornton</CTableDataCell>
                      <CTableDataCell>@fat</CTableDataCell>
                      <CTableDataCell>Ingrp</CTableDataCell>
                      <CTableDataCell>Queue</CTableDataCell>
                      <CTableDataCell>Delete</CTableDataCell>
                      <CTableDataCell>Disconnect and logout</CTableDataCell>
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

export default LiveCallsDashboard;