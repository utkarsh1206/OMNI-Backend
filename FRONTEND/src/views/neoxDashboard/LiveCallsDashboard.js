import React from 'react';
import {
    CCard,
    CCardBody,
    CCardHeader,
    CRow,
    CCol,
    CContainer
} from '@coreui/react';
import { TEXT_GREEN, TEXT_RED } from '../../config';

const LiveCallsDashboard = () => {

    const cardStyle = {
        'border': 'none',
        'boxShadow': '0 0 8px rgba(0, 0, 0, 0.2)',
        'borderRadius': '8px',
        'marginBottom': '20px',

        // 'backgroundImage': "url('../../src/assets/brand/heart.png'), linear-gradient(to left, rgb(31, 34, 42), rgb(27, 71, 197))",
        'backgroundImage': "url('../../src/assets/brand/heart.png'), linear-gradient(to left, #1F222A, #1B47C5)",
        'backgroundPosition': 'right',
        'backgroundRepeat': 'no-repeat',
        'backgroundSize': 'contain'
    }

    const cardHeaderStyle = {
        'color': '#ffffff',
        'textAlign': 'center',
        'fontWeight': 'bold',
        // 'backgroundColor': '#d2dae1',
        // 'borderBottom': 'none',
        'borderRadius': '8px 8px 0 0',
        'padding': '10px 0',
        'fontSize': '18px',
        'fontFamily': 'Brush Script MT, cursive'
    }

    const dataHeadStyle = {
        'fontSize': '18px',
        'color': 'white',
    }

    const dataValueStyle = {
        'fontSize': '40px',
        'color': 'white',
    }

    return (
        <CContainer>
            <CRow>
                <CCol sm="4">
                    <CCard style={cardStyle}>
                        <CCardHeader style={cardHeaderStyle}>Outbound Call Status</CCardHeader>
                        <CCardBody>
                            <CRow className="text-center">
                                {/* <CCol className="text-green"> */}
                                <CCol>
                                    <div style={dataHeadStyle}>In Progress</div>
                                    <div style={dataValueStyle}>0</div>
                                </CCol>
                                {/* <CCol className="text-red"> */}
                                <CCol>
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
                                <CCol>
                                    <div style={dataHeadStyle}>In Progress</div>
                                    <div style={dataValueStyle}>0</div>
                                </CCol>
                                <CCol>
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
                                <CCol>
                                    <div style={dataHeadStyle}>In Progress</div>
                                    <div style={dataValueStyle}>0</div>
                                </CCol>
                                <CCol>
                                    <div style={dataHeadStyle}>Calls In Queue</div>
                                    <div style={dataValueStyle}>0</div>
                                </CCol>
                            </CRow>
                        </CCardBody>
                    </CCard>
                </CCol>
            </CRow>
        </CContainer>
    );
}

export default LiveCallsDashboard;