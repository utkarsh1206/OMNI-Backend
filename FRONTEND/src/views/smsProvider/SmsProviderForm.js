import React, { useState, useEffect, useContext } from 'react'
import {
    CButton,
    CCard,
    CCardBody,
    CCardHeader,
    CForm,
    CFormLabel,
    CFormInput,
    CRow,
    CCol,
    CFormSelect,
    CFormCheck,
    CFormTextarea
} from '@coreui/react'
import axios from 'axios'
import { useNavigate, useParams } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'
import { SMS_URL_METHODS, SMS_CONTENT_TYPE } from './smsConfig'
import { useToast } from '../../context/ToastContext'

const FormGroup = ({ label, name, value, onChange, required, type, disabled, readOnly }) => (
    <CCol md="4" className="mb-3">
        <CFormLabel htmlFor={name}>
            {label} {required && <span style={{ color: 'red' }}>*</span>}
        </CFormLabel>
        <CFormInput
            type={type}
            id={name}
            name={name}
            value={value}
            onChange={onChange}
            required={required}
            disabled={disabled}
            readOnly={readOnly}
        />
    </CCol>
)

const SmsProviderForm = () => {
    const { id } = useParams()
    const navigate = useNavigate()
    const isEditMode = Boolean(id)
    const { authToken } = useContext(AuthContext)
    const showToast = useToast();

    const defaultFormData = {
        provider_name: "",
        method: "get",
        type: "json",
        url: "",
        query_params: "",
        max_length: "",
        success_code: "",
        enabled: true
    }

    const [formData, setFormData] = useState({ ...defaultFormData })

    useEffect(() => {
        if (isEditMode) {
            axios
                .get(`${BASE_URL}/api/sms-configurations/${id}`, {
                    headers: {
                        'Content-Type': 'application/json',
                        'Access-Control-Allow-Origin': '*',
                        Authorization: `Bearer ${authToken}`,
                    },
                })
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
                    showToast('Failed to fetch SMS provider configuration data.', 'danger');
                })
        }
    }, [id, isEditMode])

    const handleChange = (e) => {
        const { name, value } = e.target
        setFormData((prevData) => ({
            ...prevData,
            [name]: value,
        }))
    }

    const handleSubmit = async (e) => {
        e.preventDefault()
        try {
            let response
            if (isEditMode) {
                const reqPayload = {
                    "configurations": [
                        {
                            "method": formData.method,
                            "type": formData.type,
                            "url": formData.url,
                            "query_params": formData.query_params,
                            "max_length": formData.max_length || undefined,
                            "success_code": formData.success_code,
                            "enabled": formData.enabled ? "true" : "false"
                        }
                    ]
                }
                response = await axios.put(`${BASE_URL}/api/sms-configurations/${id}`, reqPayload, {
                    headers: {
                        'Content-Type': 'application/json',
                        'Access-Control-Allow-Origin': '*',
                        Authorization: `Bearer ${authToken}`,
                    },
                })
            } else {
                const reqPayload = {
                    "provider_name": formData.provider_name,
                    "configurations": [
                        {
                            "method": formData.method,
                            "type": formData.type,
                            "url": formData.url,
                            "query_params": formData.query_params,
                            "max_length": formData.max_length || undefined,
                            "success_code": formData.success_code,
                            "enabled": formData.enabled ? 'true' : 'false'
                        }
                    ]
                }
                response = await axios.post(`${BASE_URL}/api/sms-configurations`, reqPayload, {
                    headers: {
                        'Content-Type': 'application/json',
                        'Access-Control-Allow-Origin': '*',
                        Authorization: `Bearer ${authToken}`,
                    },
                })
            }

            if (response.status === 200) {
                showToast(`SMS provider configuration ${isEditMode ? "updated" : "created"} successfully!`, 'success');
                navigate('/sms_provider')
            }
            handleCancel()
        } catch (error) {
            if (error?.response?.data?.message) {
                showToast(error.response.data.message, 'danger');
            } else {
                showToast('An error occurred while processing your request.', 'danger');
            }
        }
    }

    const handleCancel = () => {
        setFormData({ ...defaultFormData })
        navigate('/sms_provider')
    }

    return (
        <CForm onSubmit={handleSubmit}>
            <CRow>
                <CCol xs="12" md="12">
                    <CCard>
                        <CCardHeader>{isEditMode ? 'Edit SMS Configuration' : 'Create SMS Configuration'}</CCardHeader>
                        <CCardBody>
                            <CRow>
                                <FormGroup
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
                                        {/* <span style={{ color: 'red' }}>
                                        {" *Note :"}
                                        </span>
                                        <span style={{ color: '#888', fontSize: '12px' }}>
                                            {" Mandotary to add {{newToken}} and {{destination}} parameters in your content to generate dynamic messages"}
                                        </span> */}
                                    </CFormLabel>
                                    <CFormTextarea
                                        id="query_params"
                                        name="query_params"
                                        value={formData.query_params}
                                        onChange={handleChange}
                                        required
                                    />
                                </CCol>
                                <FormGroup
                                    label="SMS API Message Max Length"
                                    name="max_length"
                                    type="number"
                                    value={formData.max_length}
                                    onChange={handleChange}
                                />
                                <FormGroup
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
                                        checked={[true, "1"].includes(formData.enabled) ? true : false}
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

            {/* Buttons */}
            <CRow className="mt-2 mb-4">
                <CCol xs="12" md="12" className="d-flex justify-content-end">
                    <CButton type="submit" color="primary" className="me-2">
                        {isEditMode ? 'Update' : 'Submit'}
                    </CButton>
                    <CButton type="button" color="secondary" onClick={handleCancel}>
                        Cancel
                    </CButton>
                </CCol>
            </CRow>
        </CForm>
    )
}

export default SmsProviderForm