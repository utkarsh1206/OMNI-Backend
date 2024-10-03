import React, { useEffect, useState } from 'react';
import { CForm, CFormInput, CCard, CCardHeader, CCardTitle, CRow, CCol, CFormLabel, CCardBody, CFormSelect } from '@coreui/react';

const Step2 = ({ formData, setFormData }) => {
    // const [msc_on_hld, setMsc_on_hld] = useState('');
    // const [welcomeMsg, setWelcomeMsg] = useState('');

    const [msc_on_hld, setMsc_on_hld] = useState(formData.msc_on_hld);
    const [welcomeMsg, setWelcomeMsg] = useState(formData.welcomeMsg);

    useEffect(() => {
        setFormData(prev => ({ ...prev, msc_on_hld, welcomeMsg }));
    }, [msc_on_hld, welcomeMsg, setFormData]);

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
                        <CFormLabel htmlFor="msc_on_hld" className="col-sm-2 col-form-label">Park Music-on-Hold file</CFormLabel>
                        <CCol sm={10}>
                            <CFormInput type="file" id='msc_on_hld' text="(Please upload file with .wav or .mp3 extension)" accept='.wav, .mp3' value={msc_on_hld}
                                onChange={(e) => setMsc_on_hld(e.target.value)} />
                        </CCol>
                    </CRow>
                    <CRow className="mb-3">
                        <CFormLabel htmlFor="welcomeMsg" className="col-sm-2 col-form-label">Play Welcome Message</CFormLabel>
                        <CCol sm={10}>
                            <CFormSelect
                                id="welcomeMsg"
                                value={welcomeMsg}
                                onChange={(e) => setWelcomeMsg(e.target.value)}
                                required
                            >
                                <option >---SELECT ---</option>
                                <option value="always">ALWAYS</option>
                                <option value="never">NEVER</option>
                                <option value="if_wait_only">IF WAIT ONLY</option>
                            </CFormSelect>
                        </CCol>
                    </CRow>
                </CForm>
            </CCardBody>
        </CCard>
    );
};

export default Step2;
