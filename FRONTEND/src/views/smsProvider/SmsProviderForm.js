import React, { useState, useEffect } from 'react'
import { CCard, CCardBody, CCardHeader, CForm, CFormLabel, CFormInput, CRow, CCol, CFormSelect, CFormCheck, CFormTextarea } from '@coreui/react'
import { useNavigate, useParams } from 'react-router-dom'
import { SMS_URL_METHODS, SMS_CONTENT_TYPE, formReqPayload } from './smsConfig'
import { useToast } from '../../context/ToastContext'
import { getErrorMessage } from '../../utils/errorHandlerUtils'
import { FormInputGroup, FormSubmitAction, LoadingFormWrapper } from '../../components'
import axiosInstance from '../../utils/axiosInstance'

const SmsProviderForm = () => {
    const { id } = useParams()
    const navigate = useNavigate()
    const isEditMode = Boolean(id)
    const showToast = useToast();

    const defaultFormData = {
        provider_name: "",
        method: "get",
        type: "json",
        url: "",
        query_params: "",
        max_length: "",
        success_code: "",
        enabled: "true"
    }

    const [formData, setFormData] = useState({ ...defaultFormData });
    const [editFormLoading, setEditFormLoading] = useState(false);
    const [isSubmitting, setIsSubmitting] = useState(false)

    useEffect(() => {
        if (isEditMode) {
            setEditFormLoading(true);
            axiosInstance.get(`/sms-configurations/${id}`)
                .then((response) => {
                    const providerData = response.data.data?.[0] || {};
                    const editFormData = {
                        provider_name: providerData.provider_name
                    }
                    for (const i of providerData.configurations) {
                        editFormData[i.config_key] = i.config_value
                    }
                    setFormData(editFormData)
                })
                .catch((error) => {
                    showToast(getErrorMessage(error), 'danger');
                    navigate('/sms_provider');
                })
                .finally(() => {
                    setEditFormLoading(false);
                })
        }
    }, [id, isEditMode])

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((prevData) => ({ ...prevData, [name]: value, }));
    }

    const handleSubmit = async (e) => {
        e.preventDefault()
        try {
            const reqPayload = formReqPayload(formData);
            setIsSubmitting(true);
            if (isEditMode) {
                delete reqPayload.provider_name;
                await axiosInstance.put(`/sms-configurations/${id}`, reqPayload);
            } else {
                await axiosInstance.post('/sms-configurations', reqPayload);
            }

            showToast(`SMS provider configuration ${isEditMode ? "updated" : "created"} successfully!`, 'success');
            navigate('/sms_provider');
            handleCancel();
        } catch (error) {
            showToast(getErrorMessage(error), 'danger');
        } finally {
            setIsSubmitting(false);
        }
    }

    const handleCancel = () => {
        setFormData({ ...defaultFormData })
        navigate('/sms_provider')
    }

    return (
        <LoadingFormWrapper loading={editFormLoading}>
            <CForm onSubmit={handleSubmit} className={editFormLoading ? 'table-loading' : ''}>
                <CRow>
                    <CCol xs="12">
                        <CCard>
                            <CCardHeader>{isEditMode ? 'Edit SMS Configuration' : 'Create SMS Configuration'}</CCardHeader>
                            <CCardBody>
                                <CRow>
                                    <FormInputGroup
                                        label="SMS Provider Name"
                                        name="provider_name"
                                        type="text"
                                        value={formData.provider_name}
                                        onChange={handleChange}
                                        required
                                        readOnly={isEditMode}
                                    />
                                    <CCol md="4" className="mb-3">
                                        <CFormLabel htmlFor="method">
                                            SMS URL Method <span style={{ color: 'red' }}>*</span>
                                        </CFormLabel>
                                        <CFormSelect
                                            id="method"
                                            name="method"
                                            value={formData.method}
                                            onChange={handleChange}
                                            required
                                        >
                                            <option value="">Select Method</option>
                                            {SMS_URL_METHODS.map((method) => (
                                                <option key={method.key} value={method.key}>
                                                    {method.title}
                                                </option>
                                            ))}
                                        </CFormSelect>
                                    </CCol>
                                    <CCol md="4" className="mb-3">
                                        <CFormLabel htmlFor="type">
                                            SMS Content Type <span style={{ color: 'red' }}>*</span>
                                        </CFormLabel>
                                        <CFormSelect
                                            id="type"
                                            name="type"
                                            value={formData.type}
                                            onChange={handleChange}
                                            required
                                        >
                                            <option value="">Select Type</option>
                                            {SMS_CONTENT_TYPE.map((type) => (
                                                <option key={type.key} value={type.key}>
                                                    {type.title}
                                                </option>
                                            ))}
                                        </CFormSelect>
                                    </CCol>
                                    <CCol sm="12" className="mb-3">
                                        <CFormLabel htmlFor="url">
                                            SMS API URL <span style={{ color: 'red' }}>*</span>
                                        </CFormLabel>
                                        <CFormInput
                                            type="url"
                                            id="url"
                                            name="url"
                                            value={formData.url}
                                            onChange={handleChange}
                                            required
                                        />
                                    </CCol>
                                    <CCol sm="12" className="mb-3">
                                        <CFormLabel htmlFor="query_params">
                                            Request Parameter Static Key/Value<span style={{ color: 'red' }}>*</span>
                                        </CFormLabel>
                                        <CFormTextarea
                                            id="query_params"
                                            name="query_params"
                                            value={formData.query_params}
                                            onChange={handleChange}
                                            required
                                        />
                                    </CCol>
                                    <FormInputGroup
                                        label="SMS API Message Max Length"
                                        name="max_length"
                                        type="number"
                                        value={formData.max_length}
                                        onChange={handleChange}
                                    />
                                    <FormInputGroup
                                        label="SMS API Success Code"
                                        name="success_code"
                                        type="number"
                                        value={formData.success_code}
                                        onChange={handleChange}
                                        required
                                    />
                                    <CCol md="12">
                                        <CFormLabel htmlFor="enabled">
                                            Enable SMS API
                                        </CFormLabel>
                                        <CFormCheck
                                            id="enabled"
                                            label="Enable"
                                            checked={["true", "1"].includes(formData.enabled) ? true : false}
                                            onChange={(e) => {
                                                setFormData((prevData) => ({
                                                    ...prevData,
                                                    enabled: e.target.checked,
                                                }))
                                            }}
                                        />
                                    </CCol>
                                </CRow>
                            </CCardBody>
                        </CCard>
                    </CCol>
                </CRow>

                <FormSubmitAction
                    handleCancel={handleCancel}
                    isSubmitting={isSubmitting}
                    isEditMode={isEditMode}
                />
            </CForm>
        </LoadingFormWrapper>
    )
}

export default SmsProviderForm