import React, { useState, useEffect } from 'react'
import { CCard, CCardBody, CCardHeader, CForm, CRow, CCol } from '@coreui/react'
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
    const [isSubmitting, setIsSubmitting] = useState(false);

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
        const { name, value, type, checked } = e.target;
        setFormData((prevData) => ({
            ...prevData,
            [name]: type === "checkbox" ? checked : value,
        }));
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
                                    <FormInputGroup.InputField
                                        label="SMS Provider Name"
                                        name="provider_name"
                                        type="text"
                                        value={formData.provider_name}
                                        onChange={handleChange}
                                        required
                                        readOnly={isEditMode}
                                    />
                                    <FormInputGroup.SelectField
                                        label="SMS URL Method"
                                        name="method"
                                        value={formData.method}
                                        onChange={handleChange}
                                        options={SMS_URL_METHODS}
                                        required
                                    />
                                    <FormInputGroup.SelectField
                                        label="SMS Content Type"
                                        name="type"
                                        value={formData.type}
                                        onChange={handleChange}
                                        options={SMS_CONTENT_TYPE}
                                        required
                                    />
                                    <FormInputGroup.InputField
                                        label="SMS API URL"
                                        name="url"
                                        type="url"
                                        value={formData.url}
                                        onChange={handleChange}
                                        md="12"
                                        required
                                    />
                                    <FormInputGroup.TextareaField
                                        label="Request Parameter Static Key/Value"
                                        name="query_params"
                                        value={formData.query_params}
                                        onChange={handleChange}
                                        md="12"
                                        required
                                    />
                                    <FormInputGroup.InputField
                                        label="SMS API Message Max Length"
                                        name="max_length"
                                        type="number"
                                        value={formData.max_length}
                                        onChange={handleChange}
                                    />
                                    <FormInputGroup.InputField
                                        label="SMS API Success Code"
                                        name="success_code"
                                        type="number"
                                        value={formData.success_code}
                                        onChange={handleChange}
                                        required
                                    />
                                    <FormInputGroup.CheckboxField
                                        label="Enable SMS API"
                                        checkboxLabel="Enable"
                                        name="enabled"
                                        onChange={handleChange}
                                        checked={["true", "1", true].includes(formData.enabled) ? true : false}
                                        md="12"
                                    />
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