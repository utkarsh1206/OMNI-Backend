import { CCol, CFormLabel, CFormInput } from "@coreui/react";

const FormInputGroup = ({ label, name, value, onChange, required, type, disabled, readOnly }) => {
    return (
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
}

export default FormInputGroup;