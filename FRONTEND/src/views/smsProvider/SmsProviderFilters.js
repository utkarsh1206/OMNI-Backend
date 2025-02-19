import { CRow, CCol, CFormLabel, CFormInput, CFormSelect } from "@coreui/react";
import { SMS_STATUS, SMS_URL_METHODS } from "./smsConfig";

const SmsProviderFilters = ({ filters, setFilters }) => {
    return (
        <>
            <CRow>
                <CCol md="6" className="mb-3">
                    <CFormLabel htmlFor="provider_name">
                        SMS Provider Name
                    </CFormLabel>
                    <CFormInput
                        type="text"
                        id="provider_name"
                        name="provider_name"
                        value={filters.provider_name || ''}
                        onChange={(e) => setFilters((prev) => ({ ...prev, provider_name: e.target.value }))}
                        placeholder='Enter API Name'
                    />
                </CCol>
                <CCol md="4" className="mb-3">
                    <CFormLabel htmlFor="method">
                        SMS URL Method
                    </CFormLabel>
                    <CFormSelect
                        id="method"
                        name="method"
                        value={filters.method || ''}
                        onChange={(e) => setFilters((prev) => ({ ...prev, method: e.target.value }))}
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
                    <CFormLabel htmlFor="status">
                        SMS Status
                    </CFormLabel>
                    <CFormSelect
                        id="status"
                        name="enabled"
                        value={filters.enabled || ''}
                        onChange={(e) => setFilters((prev) => ({ ...prev, enabled: e.target.value }))}
                    >
                        <option value="">Select Status</option>
                        {SMS_STATUS.map((status) => (
                            <option key={status.key} value={status.key}>
                                {status.title}
                            </option>
                        ))}
                    </CFormSelect>
                </CCol>
            </CRow>
        </>
    )
}

export default SmsProviderFilters;