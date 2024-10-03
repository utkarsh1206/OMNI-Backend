import React, { useState, useEffect } from 'react';
import {
    CCard,
    CCardBody,
    CCardHeader,
    CCol,
    CRow,
    CButton,
    CForm,
    CFormLabel,
    CFormInput,
    CFormSelect,
    CContainer,
} from '@coreui/react';
import { Link } from 'react-router-dom';

const Campaigns = () => {

    return (
        <>
            <CRow>
                <CCol xs="12">
                    <CCard className="mb-3">
                        <CCardHeader>
                            Search for <strong>Campaigns</strong>
                        </CCardHeader>
                        <CCardBody>
                            <CForm id='campaignFilter'>
                                <CRow className="mb-1">
                                    <CCol>
                                        <CRow className="mb-2">
                                            <CFormLabel htmlFor="campID" className="col-sm-4 col-form-label">
                                                {<small>Campaign ID</small>}
                                            </CFormLabel>
                                            <CCol>
                                                <CFormInput
                                                    type="text"
                                                    id="campID"
                                                />
                                            </CCol>
                                        </CRow>
                                    </CCol>
                                    <CCol>
                                        <CRow className="mb-2 align-middle">
                                            <CFormLabel htmlFor="campName" className="col-sm-4 col-form-label">
                                                {<small>Campaign Name</small>}
                                            </CFormLabel>
                                            <CCol>
                                                <CFormInput
                                                    type="text"
                                                    id="campName"
                                                />
                                            </CCol>
                                        </CRow>
                                    </CCol>
                                    <CCol>
                                        <CRow className="mb-2 align-middle">
                                            <CFormLabel htmlFor="dialMethod" className="col-sm-4 col-form-label">
                                                {<small>Dial Method</small>}
                                            </CFormLabel>
                                            <CCol>
                                                <CFormSelect
                                                    id="dialMethod"
                                                    required
                                                >
                                                    <option value="">--- Select Dial Method ---</option>
                                                    <option value="manual">Manual</option>
                                                    <option value="preview">Preview</option>
                                                    <option value="progressive">Progressive</option>
                                                </CFormSelect>
                                            </CCol>
                                        </CRow>
                                    </CCol>
                                </CRow>
                                <div className="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <CButton type="submit" color="primary" form="campaignFilter">
                                        Submit
                                    </CButton>
                                </div>
                            </CForm>
                        </CCardBody>
                    </CCard>
                </CCol>
            </CRow>
            <h6>User Groups Listing</h6>
            <hr />
            <CContainer>
                <CRow fluid responsive>
                    <CCol md={8}>
                        <Link to="/campaign/create_campaign">
                            <CButton color="primary" className="mb-4 me-2" size="sm">
                                Create User Group
                            </CButton>
                        </Link>
                        <CButton color="primary" className="mb-4 me-2" size="sm">
                            Delete User Group
                        </CButton>
                    </CCol>
                </CRow>
            </CContainer>
        </>
    );
};

export default Campaigns;