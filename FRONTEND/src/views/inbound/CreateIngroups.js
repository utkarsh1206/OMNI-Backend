import React, { useState } from 'react';
import { CAccordion, CAccordionBody, CAccordionHeader, CAccordionItem, CCard, CCardBody, CCardHeader, CCardTitle, CCol, CForm, CFormInput, CFormLabel, CRow } from '@coreui/react';
import ValidationForm from './CreateIngroupValidation';

const CreateIngroups = () => {
    const [campID, setCampID] = useState('');
    const [campName, setCampName] = useState('');
    const [campName1, setCampName1] = useState('');
    const [groupColour, setGroupColour] = useState('');

    return (
        <>
            <CCard className='mb-4'>
                <CCardHeader>
                    <CCardTitle>
                        Create In-Groups
                    </CCardTitle>
                </CCardHeader>
                <CCardBody>
                    <CAccordion flush>
                        <CAccordionItem itemKey={1}>
                            <CAccordionHeader>Group Details</CAccordionHeader>
                            <CAccordionBody>
                                <CForm>
                                    <CRow>
                                        <CCol>
                                            <CRow className="mb-3">
                                                <CFormLabel htmlFor="campID" className="col-sm-3 col-form-label">Campaign ID&nbsp;<span style={{ color: 'red' }}>*</span></CFormLabel>
                                                <CCol sm={8}>
                                                    <CFormInput type="text"
                                                        id="campID"
                                                        value={campID}
                                                        onChange={(e) => setCampID(e.target.value)}
                                                        required
                                                    />
                                                </CCol>
                                            </CRow>
                                            <CRow className="mb-3">
                                                <CFormLabel htmlFor="campName" className="col-sm-3 col-form-label">Campaign Name&nbsp;<span style={{ color: 'red' }}>*</span></CFormLabel>
                                                <CCol sm={8}>
                                                    <CFormInput
                                                        type="text"
                                                        id="campName"
                                                        value={campName}
                                                        onChange={(e) => setCampName(e.target.value)}
                                                        required
                                                    />
                                                </CCol>
                                            </CRow>
                                        </CCol>
                                        <CCol>
                                            <CRow className="mb-3">
                                                <CFormLabel htmlFor="groupColour" className="col-sm-3 col-form-label">Group Colour&nbsp;<span style={{ color: 'red' }}>*</span></CFormLabel>
                                                <CCol sm={8}>
                                                    <CFormInput type="color"
                                                        id="groupColour"
                                                        value={groupColour}
                                                        onChange={(e) => setGroupColour(e.target.value)}
                                                        color='red'
                                                        text={<small>{groupColour}</small>}
                                                        required
                                                    />
                                                </CCol>
                                            </CRow>
                                            <CRow className="mb-3">
                                                <CFormLabel htmlFor="campName1" className="col-sm-3 col-form-label">Campaign Name&nbsp;<span style={{ color: 'red' }}>*</span></CFormLabel>
                                                <CCol sm={8}>
                                                    <CFormInput
                                                        type="text"
                                                        id="campName1"
                                                        value={campName1}
                                                        onChange={(e) => setCampName1(e.target.value)}
                                                        required
                                                    />
                                                </CCol>
                                            </CRow>
                                        </CCol>
                                    </CRow>
                                </CForm>
                            </CAccordionBody>
                        </CAccordionItem>
                        <CAccordionItem itemKey={2}>
                            <CAccordionHeader>Call Route on Hangup</CAccordionHeader>
                            <CAccordionBody>
                                <ValidationForm />
                            </CAccordionBody>
                        </CAccordionItem>
                        <CAccordionItem itemKey={3}>
                            <CAccordionHeader>Prompt Files</CAccordionHeader>
                            <CAccordionBody>
                                <h5>Step 3</h5>
                            </CAccordionBody>
                        </CAccordionItem>
                        <CAccordionItem itemKey={4}>
                            <CAccordionHeader>Wait Options</CAccordionHeader>
                            <CAccordionBody>
                                <h5>Step 4</h5>
                            </CAccordionBody>
                        </CAccordionItem>
                        <CAccordionItem itemKey={5}>
                            <CAccordionHeader>Advertise Detail</CAccordionHeader>
                            <CAccordionBody>
                                <h5>Step 5</h5>
                            </CAccordionBody>
                        </CAccordionItem>
                        <CAccordionItem itemKey={6}>
                            <CAccordionHeader>Ring Timeout</CAccordionHeader>
                            <CAccordionBody>
                                <h5>Step 6</h5>
                            </CAccordionBody>
                        </CAccordionItem>
                        <CAccordionItem itemKey={7}>
                            <CAccordionHeader>Use DNC Option</CAccordionHeader>
                            <CAccordionBody>
                                <h5>Step 7</h5>
                            </CAccordionBody>
                        </CAccordionItem>
                    </CAccordion>
                </CCardBody>
            </CCard>
        </>
    );
};

export default CreateIngroups;