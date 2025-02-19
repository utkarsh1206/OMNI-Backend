import { CRow } from "@coreui/react";
import { SMS_STATUS, SMS_URL_METHODS } from "./smsConfig";
import { FormInputGroup } from "../../components";

const SmsProviderFilters = ({ filters, setFilters }) => {
    const handleChange = (e) => {
        const { name, value } = e.target;
        setFilters((prevData) => ({ ...prevData, [name]: value }));
    }

    return (
        <>
            <CRow>
                <FormInputGroup.InputField
                    label="SMS Provider Name"
                    name="provider_name"
                    type="text"
                    value={filters.provider_name || ''}
                    onChange={handleChange}
                    md="6"
                />
                <FormInputGroup.SelectField
                    label="SMS URL Method"
                    name="method"
                    value={filters.method || ''}
                    onChange={handleChange}
                    options={SMS_URL_METHODS}
                />
                <FormInputGroup.SelectField
                    label="SMS Status"
                    name="enabled"
                    value={filters.enabled || ''}
                    onChange={handleChange}
                    options={SMS_STATUS}
                />
            </CRow>
        </>
    )
}

export default SmsProviderFilters;