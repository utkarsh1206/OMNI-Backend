import React, { useState, useContext, useEffect } from 'react';
import {
  CForm,
  CFormTextarea,
  CButton,
  CRow,
  CCol,
  CCard,
  CCardBody,
  CCardHeader,
  CTable,
  CTableHead,
  CTableBody,
  CTableRow,
  CTableDataCell,
  CTableHeaderCell,
  CCardTitle
} from '@coreui/react';
import axios from 'axios';
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'

const License = () => {
  const { authToken } = useContext(AuthContext)
  const [licenseKey, setLicenseKey] = useState('');
  const [message, setMessage] = useState('');
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [licenseInfo, setLicenseInfo] = useState(null);
  const [isTenant, setIsTenant] = useState(null);

  const handleInputChange = (event) => {
    setLicenseKey(event.target.value);
  };

  const handleSubmit = async (event) => {
    event.preventDefault();

    setLoading(true);
    setError(null);

    try {
      const response = await axios.post(`${BASE_URL}/api/license_update`,
        {
          license_key: licenseKey
        }, {
        headers: {
          Authorization: `Bearer ${authToken}`,
        },
      })

      alert('License Applied Successfully');
      setMessage(response.data.message);
    } catch (err) {
      // Handle error
      setError(err.response?.data?.message || 'An error occurred');
      console.error('Error:', err);
    } finally {
      setLoading(false);
    }
  };

  const clearLicense = () => {
    setLicenseKey('');
  };

  const fetchLicenseInfo = async () => {
    try {
      const response = await axios.get(`${BASE_URL}/api/decode-license`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
        },
      });

      console.log(response.data);
      setLicenseInfo(response.data);
      setLicenseKey(response.data.license_key);
      if (response.data.is_tanant !== undefined) {
        setIsTenant(response.data.is_tanant); // Set isTenant based on API response
      } else {
        setError("Tenant information not available in the response.");
      }
    } catch (err) {
      if (err.response.data.adminFlag == 1) {
        setIsTenant(false);
      } else {
        setIsTenant(true)
      }
      // setIsTenant(false);
      setError(err.response?.data?.message || 'Failed to fetch license info');
      console.error('Error fetching license info:', err);
    }
  };

  useEffect(() => {
    // Fetch the current license information when the component is mounted
    fetchLicenseInfo();
  }, []);


  return (
    <CRow>
      <CCol xs="12" md="12">
        <CCard>
          <CCardHeader>
            <CCardTitle>License Detail</CCardTitle>
          </CCardHeader>
          <CCardBody>
            {/* {message && <div style={{ color: 'green' }}>{message}</div>}
            {error && <div style={{ color: 'red' }}>{error}</div>} */}
            <CForm id='licenseForm' onSubmit={handleSubmit}>
              <CRow>
                <CCol md="8" className="mb-3">
                  <label htmlFor="licenseKey" className="form-label">
                    License Key <span className="text-danger">*</span>
                  </label>
                  <CFormTextarea
                    id="licenseKey"
                    value={licenseKey}
                    onChange={handleInputChange}
                    required
                    rows={5}
                    disabled={isTenant}
                  />
                </CCol>
              </CRow>
              {!isTenant && (
              <CRow className="mt-2 mb-4">
                <CCol xs="12" md="12">
                  <CButton type="submit" color="primary" className="me-2">
                    {loading ? 'Updating...' : 'Update License'}
                  </CButton>
                  <CButton type="button" color="secondary" onClick={clearLicense}>
                    Clear
                  </CButton>
                </CCol>
              </CRow>
              )}
            </CForm>

            {/* License Info Table */}
            {licenseInfo && !isTenant &&(
              <CTable bordered className="mt-2" style={{ width: '70%' }}>
                <CTableHead>
                  <CTableRow>
                    <CTableHeaderCell colSpan={2}>
                      License Information
                    </CTableHeaderCell>
                  </CTableRow>
                </CTableHead>
                <CTableBody>
                  <CTableRow>
                    <CTableDataCell>Validity Date</CTableDataCell>
                    <CTableDataCell>{licenseInfo.validity_date}</CTableDataCell>
                  </CTableRow>
                  <CTableRow>
                    <CTableDataCell>Number of Enterprise</CTableDataCell>
                    <CTableDataCell>{licenseInfo.number_of_enterprise}</CTableDataCell>
                  </CTableRow>
                  <CTableRow>
                    <CTableDataCell>Number of Agents</CTableDataCell>
                    <CTableDataCell>{licenseInfo.number_of_agents}</CTableDataCell>
                  </CTableRow>
                  <CTableRow>
                    <CTableDataCell>Number of Live Agents</CTableDataCell>
                    <CTableDataCell>{licenseInfo.number_of_live_agents}</CTableDataCell>
                  </CTableRow>
                  <CTableRow>
                    <CTableDataCell>Number of ACL Users</CTableDataCell>
                    <CTableDataCell>{licenseInfo.number_of_acl_users}</CTableDataCell>
                  </CTableRow>
                </CTableBody>
              </CTable>
            )}
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>
  )
}

export default License