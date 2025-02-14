import React, { useState, useContext, useEffect } from 'react';
import {
  CInputGroup,
  CInputGroupText,
  CFormInput,
  CButton,
  CCol,
  CRow,
  CCard,
  CCardBody,
  CCardHeader,
  CCardTitle,
  CForm,
  CFormTextarea
} from '@coreui/react';
import axios from 'axios';
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'
import { useNavigate, useParams } from 'react-router-dom'

const TenantsLicenseForm = () => {
  const { authToken } = useContext(AuthContext)
  const navigate = useNavigate()
  const { id } = useParams()
  const [formData, setFormData] = useState({
    enterpriseName: '',
    expireDate: '',
    agentCount: '',
    liveAgentCount: '',
    managerCount: '',
    availableAgentCount: '',
    availableLiveAgentCount: '',
    availableManagerCount: ''
  });
  const [loading, setLoading] = useState(false);
  const [licenseNotFound, setLicenseNotFound] = useState(false);

  useEffect(() => {
    const fetchEnterpriseName = async () => {
      setLoading(true);
      setLicenseNotFound(false);

      try {
        const response = await axios.get(`${BASE_URL}/api/tenants/${id}`, {
          headers: {
            Authorization: `Bearer ${authToken}`
          }
        });

        setFormData((prevData) => ({
          ...prevData,
          enterpriseName: response.data.data.company_name,
        }));


        const licenseResponse = await axios.get(`${BASE_URL}/api/tenant/${id}/license`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        });

        // if (licenseResponse.data && licenseResponse.data.data) {
        setFormData((prevData) => ({
          ...prevData,
          expireDate: licenseResponse.data.data.expire_date,
          agentCount: licenseResponse.data.data.agent_ct,
          liveAgentCount: licenseResponse.data.data.live_agent_ct,
          managerCount: licenseResponse.data.data.manager_ct,
          availableAgentCount: licenseResponse.data.data.available_agents,
          availableLiveAgentCount: licenseResponse.data.data.available_live_agents,
          availableManagerCount: licenseResponse.data.data.available_acl_users,
          licenseKey: licenseResponse.data.data.license_key,
        }));
        // } else {
        //   setLicenseNotFound(true);
        // }
      } catch (error) {
        console.error('Error fetching enterprise name:', error);
      } finally {
        setLoading(false);
      }
    };

    if (id) {
      fetchEnterpriseName();
    }
  }, [id, authToken]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData((prevData) => ({
      ...prevData,
      [name]: value
    }));
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    const { expireDate, agentCount, liveAgentCount, managerCount } = formData;
    const licenseData = {
      expire_date: expireDate,
      agent_ct: agentCount,
      live_agent_ct: liveAgentCount,
      manager_ct: managerCount,
    };

    try {
      setLoading(true);
      const response = await axios.put(`${BASE_URL}/api/tenant-license/${id}`,
        licenseData,
        {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        }
      );

      alert(response.data.message);
      navigate(`/tenants`);
    } catch (error) {
      console.error('Error updating license:', error);
      if (error.response && error.response.data && error.response.data.message) {
        alert(error.response.data.message);
      } else {
        alert("An unexpected error occurred.");
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <CRow>
      <CCol md='6'>
        <CCard className='mb-4 shadow-sm'>
          <CCardHeader>
            <CCardTitle>Create Enterprise License</CCardTitle>
          </CCardHeader>
          <CCardBody>
            <CForm onSubmit={handleSubmit}>
              {/* Enterprise License Details Section */}
              <CRow className="mb-3">
                {/* Enterprise ID Input */}
                <CCol md="12">
                  <CInputGroup>
                    <CInputGroupText style={{ width: '40%' }}>Enterprise</CInputGroupText>
                    <CFormInput
                      value={formData.enterpriseName || 'Loading...'}
                      readOnly
                      disabled
                      placeholder="Enterprise Name will be displayed here"
                    />
                  </CInputGroup>
                </CCol>
              </CRow>

              {/* Expiry Date Input */}
              <CRow className="mb-3">
                <CCol md="12">
                  <CInputGroup>
                    <CInputGroupText style={{ width: '40%' }}>Expire Date</CInputGroupText>
                    <CFormInput
                      type="date"
                      value={formData.expireDate}
                      onChange={handleChange}
                      name="expireDate"
                      placeholder="Enter Expiry Date"
                    />
                  </CInputGroup>
                </CCol>
              </CRow>

              <CRow className="mb-3">
                <CCol md="6">
                  <CInputGroup>
                    <CInputGroupText style={{ width: '60%' }}>Agent Count</CInputGroupText>
                    <CFormInput
                      value={formData.agentCount}
                      onChange={handleChange}
                      name="agentCount"
                      placeholder="Enter Agent Count"
                    />
                  </CInputGroup>
                </CCol>
                <CCol md="6">
                  <CInputGroup>
                    <CInputGroupText style={{ width: '62%' }}>Available Agent Count</CInputGroupText>
                    <CFormInput
                      value={formData.availableAgentCount}
                      disabled={true}
                      readOnly
                    />
                  </CInputGroup>
                </CCol>
              </CRow>

              {/* Live Agent Count and Available Live Agent Count Inline */}
              <CRow className="mb-3">
                <CCol md="6">
                  <CInputGroup>
                    <CInputGroupText style={{ width: '60%' }}>Live Agent Count</CInputGroupText>
                    <CFormInput
                      value={formData.liveAgentCount}
                      onChange={handleChange}
                      name="liveAgentCount"
                      placeholder="Enter Live Agent Count"
                    />
                  </CInputGroup>
                </CCol>
                <CCol md="6">
                  <CInputGroup>
                    <CInputGroupText style={{ width: '62%' }}>Available Live Agent Count</CInputGroupText>
                    <CFormInput
                      value={`${formData.availableLiveAgentCount}`}
                      disabled={true}
                      readOnly
                    />
                  </CInputGroup>
                </CCol>
              </CRow>

              {/* Manager Count and Available Manager Count Inline */}
              <CRow className="mb-3">
                <CCol md="6">
                  <CInputGroup>
                    <CInputGroupText style={{ width: '60%' }}>Manager Count</CInputGroupText>
                    <CFormInput
                      value={formData.managerCount}
                      onChange={handleChange}
                      name="managerCount"
                      placeholder="Enter Manager Count"
                    />
                  </CInputGroup>
                </CCol>
                <CCol md="6">
                  <CInputGroup>
                    <CInputGroupText style={{ width: '62%' }}>Available Manager Count</CInputGroupText>
                    <CFormInput
                      value={`${formData.availableManagerCount}`}
                      disabled={true}
                      readOnly
                    />
                  </CInputGroup>
                </CCol>
              </CRow>

              {/* Submit Button */}
              <CRow className="text-center">
                <CCol>
                  <CButton type="submit" color="primary" className="me-2" disabled={loading}>
                    {loading ? 'Submitting...' : 'Submit'}
                  </CButton>
                  <CButton
                    color="secondary"
                    onClick={() => navigate('/tenants')}
                    className="me-2"
                  >
                    Back
                  </CButton>
                </CCol>
              </CRow>
            </CForm>
          </CCardBody>
        </CCard >
      </CCol>
      <CCol md='6'>
        <CCard className='mb-4 shadow-sm'>
          <CCardHeader>
            <CCardTitle>License Key</CCardTitle>
          </CCardHeader>
          <CCardBody>
            <CFormTextarea value={formData.licenseKey} readOnly disabled rows={5} />
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>

  )
}

export default TenantsLicenseForm