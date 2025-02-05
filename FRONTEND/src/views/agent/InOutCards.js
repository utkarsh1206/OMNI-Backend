import React from 'react';
import {
  CCard,
  CCardBody,
  CCardHeader,
  CCol,
  CRow,
} from '@coreui/react';
import '@coreui/coreui/dist/css/coreui.min.css';
import heart from '../../assets/brand/heart.png';

const InOutCards = () => {

  const vars = {
    'backgroundImage': `url(${heart}), linear-gradient(to left, #1F222A, #1B47C5)`,
    'backgroundPosition': 'right',
    'backgroundRepeat': 'no-repeat',
    'backgroundSize': 'contain'
  }
  const vars2 = {
    // 'backgroundImage': "url('../../src/assets/brand/heart.png'), linear-gradient(270deg, #1F222A 0%, #c32323 100%)",
    'backgroundImage': `url(${heart}), linear-gradient(270deg, #1B47C5 0%, #1F222A 100%)`,
    'backgroundPosition': 'right',
    'backgroundRepeat': 'no-repeat',
    'backgroundSize': 'contain'
  }

  return (
    <CRow>
      <CCol className='mb-2' xs="12" sm="6" lg="6">
        <CCard style={vars} textBgColor='dark'>
          <CCardHeader>
            <h3>1.2k</h3>
            <h5>Inbound Calls</h5>
          </CCardHeader>
          <CCardBody>
            <h3>935</h3>
            <h6>Problems Solved</h6>
          </CCardBody>
        </CCard>
      </CCol>
      <CCol className='mb-2' xs="12" sm="6" lg="6">
        <CCard style={vars2} textBgColor='dark'>
          <CCardHeader>
            <h3>427</h3>
            <h5>Outbound Calls</h5>
          </CCardHeader>
          <CCardBody>
            <h3>386</h3>
            <h6>Generated Leads</h6>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>
  );
};

export default InOutCards;
