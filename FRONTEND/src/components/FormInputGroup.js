import { CCol, CFormLabel, CFormInput, CFormSelect, CFormCheck, CFormTextarea } from "@coreui/react";

const InputField = ({ label, name, value, onChange, required, type, disabled, readOnly, md = "4" }) => (
    <CCol md={md} className="mb-3">
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
);

const SelectField = ({ label, name, value, options, onChange, required, md = "4" }) => (
    <CCol md={md} className="mb-3">
        <CFormLabel htmlFor={name}>
            {label} {required && <span style={{ color: 'red' }}>*</span>}
        </CFormLabel>
        <CFormSelect
            id={name}
            name={name}
            value={value}
            onChange={onChange}
            required={required}
        >
            <option value="">Select {label}</option>
            {options.map((option) => (
                <option key={option.key} value={option.key}>
                    {option.title}
                </option>
            ))}
        </CFormSelect>
    </CCol>
);

const CheckboxField = ({ label, checkboxLabel, name, checked, onChange, md = "4" }) => (
    <CCol md={md} className="mb-3">
        <CFormLabel htmlFor={name}>
            {label}
        </CFormLabel>
        <CFormCheck
            id={name}
            name={name}
            label={checkboxLabel}
            checked={checked}
            onChange={onChange}
        />
    </CCol>
);

const TextareaField = ({ label, name, value, onChange, required, md = "4" }) => (
    <CCol md={md} className="mb-3">
        <CFormLabel htmlFor={name}>
            {label} {required && <span style={{ color: 'red' }}>*</span>}
        </CFormLabel>
        <CFormTextarea
            id={name}
            name={name}
            value={value}
            onChange={onChange}
            required={required}
        />
    </CCol>
);

export default {
    InputField,
    SelectField,
    CheckboxField,
    TextareaField
}