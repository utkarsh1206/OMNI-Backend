import React, { useState, useEffect, useContext } from 'react'
import {
  CButton,
  CCol,
  CContainer,
  CRow,
  CCard,
  CCardBody,
  CCardHeader,
  CForm,
  CFormInput,
  CFormLabel,
  CFormSelect,
  CFormText,
  CFormCheck
} from '@coreui/react';
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'
import axios from 'axios'
import { useNavigate, useParams } from 'react-router-dom'

const PhonesForm = () => {
  const { authToken } = useContext(AuthContext)
  const navigate = useNavigate()
  const { id } = useParams()  // If you are in edit mode
  const isEditMode = Boolean(id)

  const [extension, setExtension] = useState('');
  const [login, setLogin] = useState('');
  const [pass, setPassword] = useState('');
  const [status, setStatus] = useState('ACTIVE');
  const [local_gmt, setLocalGmt] = useState('5.50');

  useEffect(() => {
    if (isEditMode) {
      const fetchPhoneData = async () => {
        try {
          const response = await axios.get(`${BASE_URL}/api/phones/${id}`, {
            headers: {
              Authorization: `Bearer ${authToken}`,
            },
          });

          const phoneData = response.data.data;
          setExtension(phoneData.extension);
          setLogin(phoneData.login);
          setPassword(phoneData.pass);
          setStatus(phoneData.status === 'ACTIVE');
          setLocalGmt(phoneData.local_gmt);
        } catch (error) {
          console.error('Error fetching phone data:', error);
          alert('Failed to fetch phone data.');
        }
      };

      fetchPhoneData();
    }
  }, [id, isEditMode, authToken]);

  const handleSubmit = async (event) => {
    event.preventDefault();

    // Prepare the form data
    const formData = {
      extension,
      login,
      pass,
      status: status ? 'ACTIVE' : 'INACTIVE',
      local_gmt: local_gmt || '5.50'
    };

    try {
      let response;
      if (isEditMode) {
        response = await axios.put(`${BASE_URL}/api/phones/${id}`, formData, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      } else {
        response = await axios.post(`${BASE_URL}/api/phones`, formData, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
      }

      alert(response.data.message)
      navigate('/phones')

    } catch (error) {
      alert(error.response.data.message)
      // Handle error
      console.error('Error adding phone:', error);
    }
  };

  const handleCancel = () => {
    navigate('/phones');  // Navigate back to the phones list page
  };

  return (
    <CForm onSubmit={handleSubmit}>
      <CRow>
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>
              <h5>{isEditMode ? 'Update' : 'Create'} Phones</h5>
            </CCardHeader>
            <CCardBody>
              <CRow>
                <CCol md="4">
                  <CFormLabel htmlFor="extension">
                    Extension <span className="text-danger">*</span>
                  </CFormLabel>
                  <CFormInput
                    type="text"
                    id="extension"
                    placeholder="Enter phone extension"
                    value={extension}
                    onChange={(e) => setExtension(e.target.value)}
                    disabled={isEditMode}
                  />
                  {/* <CFormText muted>(digits only)</CFormText> */}
                </CCol>

                <CCol md="4">
                  <CFormLabel htmlFor="login">
                    Phone <span className="text-danger">*</span>
                  </CFormLabel>
                  <CFormInput
                    type="text"
                    id="login"
                    placeholder="Enter Phone number for login"
                    value={login}
                    onChange={(e) => setLogin(e.target.value)}
                  />
                </CCol>

                <CCol md="4">
                  <CFormLabel htmlFor="password">
                    Phone Password <span className="text-danger">*</span>
                  </CFormLabel>
                  <CFormInput
                    type="password"
                    id="password"
                    placeholder="Enter password"
                    value={pass}
                    onChange={(e) => setPassword(e.target.value)}
                  />
                </CCol>
              </CRow>

              {/* Status Field */}
              <CRow className="mb-3">
                <CCol md="4">
                  <CFormLabel htmlFor="status">Status</CFormLabel>
                  <CFormCheck
                    id="status"
                    label="ACTIVE"
                    checked={status}
                    onChange={(e) => setStatus(e.target.checked)}
                  />
                </CCol>

                <CCol md="4">
                  <CFormLabel htmlFor="local_gmt">Local GMT</CFormLabel>
                  <CFormSelect
                    id="local_gmt"
                    value={local_gmt}
                    onChange={(e) => setLocalGmt(e.target.value)}
                  >
                    <option value="12.75">12.75</option>
                    <option value="12.00">12.00</option>
                    <option value="11.00">11.00</option>
                    <option value="10.00">10.00</option>
                    <option value="9.50">9.50</option>
                    <option value="9.00">9.00</option>
                    <option value="8.00">8.00</option>
                    <option value="7.00">7.00</option>
                    <option value="6.50">6.50</option>
                    <option value="6.00">6.00</option>
                    <option value="5.75">5.75</option>
                    <option value="5.50">5.50</option>
                    <option value="5.30">5.30</option>
                    <option value="5.00">5.00</option>
                    <option value="4.50">4.50</option>
                    <option value="4.00">4.00</option>
                    <option value="3.50">3.50</option>
                    <option value="3.00">3.00</option>
                    <option value="2.00">2.00</option>
                    <option value="1.00">1.00</option>
                    <option value="0.00">0.00</option>
                    <option value="-1.00">-1.00</option>
                    <option value="-2.00">-2.00</option>
                    <option value="-3.00">-3.00</option>
                    <option value="-3.50">-3.50</option>
                    <option value="-4.00">-4.00</option>
                    <option value="-5.00">-5.00</option>
                    <option value="-6.00">-6.00</option>
                    <option value="-7.00">-7.00</option>
                    <option value="-8.00">-8.00</option>
                    <option value="-9.00">-9.00</option>
                    <option value="-10.00">-10.00</option>
                    <option value="-11.00">-11.00</option>
                    <option value="-12.00">-12.00</option>
                  </CFormSelect>
                  {/* <CFormText muted>(Do NOT Adjust for DST)</CFormText> */}
                </CCol>
              </CRow>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      {/* Submit Button */}
      <CRow className="mt-2 mb-4">
        <CCol xs="12" md="12" className="d-flex justify-content-end">
          <CButton type="submit" color="primary" className="me-2">
            {isEditMode ? 'Update' : 'Submit'}
          </CButton>
          <CButton type="button" color="secondary"
            onClick={handleCancel}
          >
            Cancel
          </CButton>
        </CCol>
      </CRow>
    </CForm>
  )
}

export default PhonesForm