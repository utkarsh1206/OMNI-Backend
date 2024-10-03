import React, { useEffect, useState } from 'react';
import { CForm, CFormInput, CCard, CCardHeader, CCardTitle, CRow, CCol, CFormLabel, CCardBody } from '@coreui/react';

const Step1 = ({ formData, setFormData }) => {
    // const [campID, setCampID] = useState('');
    // const [campName, setCampName] = useState('');

    const [campID, setCampID] = useState(formData.campID);
    const [campName, setCampName] = useState(formData.campName);

    useEffect(() => {
        setFormData(prev => ({ ...prev, campID, campName }));
    }, [campID, campName, setFormData]);

    return (
        <CCard className="mt-4">
            <CCardHeader>
                <CCardTitle>
                    Campaign Detail
                </CCardTitle>
            </CCardHeader>
            <CCardBody>
                <CForm>
                    <CRow className="mb-3">
                        <CFormLabel htmlFor="campID" className="col-sm-2 col-form-label">Campaign ID&nbsp;<span style={{ color: 'red' }}>*</span></CFormLabel>
                        <CCol sm={10}>
                            <CFormInput type="text"
                                id="campID"
                                value={campID}
                                onChange={(e) => setCampID(e.target.value)}
                                required
                            />
                        </CCol>
                    </CRow>
                    <CRow className="mb-3">
                        <CFormLabel htmlFor="campName" className="col-sm-2 col-form-label">Campaign Name&nbsp;<span style={{ color: 'red' }}>*</span></CFormLabel>
                        <CCol sm={10}>
                            <CFormInput
                                type="text"
                                id="campName"
                                value={campName}
                                onChange={(e) => setCampName(e.target.value)}
                                required
                            />
                        </CCol>
                    </CRow>
                </CForm>
            </CCardBody>
        </CCard>
    );
};

export default Step1;
