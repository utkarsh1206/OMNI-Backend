import React, { useContext, useState } from 'react'
import {
    CButton,
    CCol,
    CContainer,
    CFormInput,
    CRow,
    CTableDataCell,
    CTableRow,
    CTooltip,
    CFormLabel,
    CFormSelect
} from '@coreui/react'
import { Link, useNavigate } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { cilPencil, cilXCircle } from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../context/AuthContext'
import PaginationComponent from '../../components/PaginationComponent'
import axios from 'axios'
import SearchAndFilter from '../../components/SearchAndFilter'
import { SMS_STATUS, SMS_URL_METHODS } from './smsConfig'
import LoadingTable from '../../components/LoadingTable'

const SmsProvider = () => {
    const navigate = useNavigate()
    const [providers, setProviders] = useState([])
    const { authToken } = useContext(AuthContext)
    const [searchQuery, setSearchQuery] = useState('')
    const [loading, setLoading] = useState(false);

    const [filters, setFilters] = useState({
        provider_name: '',
        method: '',
        enabled: ''
    })

    const [currentPage, setCurrentPage] = useState(1) // Add state to track the current page
    const [perPage, setPerPage] = useState(10) // Add state to track the current page
    const [appliedFilters, setAppliedFilters] = useState({})

    const handleApplyFilters = (newFilters) => {
        setAppliedFilters(newFilters) // Update applied filters only on button click
    }
    const handleResetFilters = () => {
        setFilters({}) // Clear filter state
        setAppliedFilters({}) // Clear applied filters
        setSearchQuery('') // Clear live search
    }

    const handlePageChange = (page) => {
        setCurrentPage(page) // Update the current page when pagination changes
    }
    const handlePerPageValue = (perPage) => {
        setPerPage(perPage) // Update the current page when pagination changes
    }

    const handleDeleteProvider = async (id, apiName) => {
        if (window.confirm('Are you sure to delete ' + apiName + '?')) {
            try {
                let response = await axios.delete(`${BASE_URL}/api/sms-configurations/${id}`, {
                    headers: {
                        Authorization: `Bearer ${authToken}`,
                    },
                })

                if (response.status === 200 || response.status === 204) {
                    alert(response.data.message)
                    // Optionally, refetch the data
                    setProviders((prev) => prev.filter((provider) => provider.id !== id)) // Immediately update the UI
                }
            } catch (error) {
                alert('An error occurred while processing your request.')
            }
        }
    }

    const setSmsProviderListingData = (data) => {
        const formatedData = [];
        for (const i of data) {
            if (!i.configurations || !i?.configurations?.length) {
                continue;
            }

            const data = { provider_name: i.provider_name, id: i.provider_id };

            for (const j of i.configurations) {
                data[j.config_key] = j.config_value
            }
            formatedData.push(data);
        }
        setProviders(formatedData);
    }

    const renderProviderFilters = () => (
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

    return (
        <>
            <CRow>
                <CCol>
                    <CContainer>
                        <CRow>
                            <CCol md={8}>
                                <Link to="/sms_provider/crt_sms_provider">
                                    <CButton color="primary" className="me-2" size="sm">
                                        Create SMS Provider
                                    </CButton>
                                </Link>
                            </CCol>
                            <SearchAndFilter
                                onSearch={setSearchQuery} // Callback for live search
                                onApplyFilters={handleApplyFilters}
                                onResetFilters={handleResetFilters} // Callback for reset filters
                                filters={filters} // Current filters
                                setFilters={setFilters} // Update filters
                                renderFilters={renderProviderFilters} // Custom filter UI for users
                            />
                        </CRow>
                    </CContainer>
                    <hr />

                    <LoadingTable
                        loading={loading}
                        dataLength={providers.length}
                        tableHeaders={[
                            "#",
                            "Provider Name",
                            "URL Method",
                            "Content Type",
                            "API URL",
                            "Message Max Length",
                            "Controls"
                        ]}
                    >
                        {providers.map((provider, index) => (
                            <CTableRow key={provider.id}>
                                <CTableDataCell>{index + 1}</CTableDataCell>
                                <CTableDataCell>{provider.provider_name}</CTableDataCell>
                                <CTableDataCell>{provider.method}</CTableDataCell>
                                <CTableDataCell>{provider.type}</CTableDataCell>
                                <CTableDataCell>{provider.url}</CTableDataCell>
                                <CTableDataCell>{provider.max_length}</CTableDataCell>
                                <CTableDataCell>
                                    <CRow className="justify-content-center mx-0">
                                        <CCol className="mx-auto d-flex justify-content-center">
                                            <CTooltip content="Edit">
                                                <CButton
                                                    color="warning"
                                                    size="sm"
                                                    onClick={() => navigate(`/sms_provider/edit/${provider.id}`)}
                                                >
                                                    <CIcon icon={cilPencil} />
                                                </CButton>
                                            </CTooltip>
                                        </CCol>
                                        <CCol className="mx-auto d-flex justify-content-center">
                                            <CTooltip content="Delete">
                                                <CButton
                                                    color="danger"
                                                    size="sm"
                                                    onClick={() => handleDeleteProvider(provider.id, provider.provider_name)}
                                                >
                                                    <CIcon icon={cilXCircle} />
                                                </CButton>
                                            </CTooltip>
                                        </CCol>
                                    </CRow>
                                </CTableDataCell>
                            </CTableRow>
                        ))}
                    </LoadingTable>

                    {/* Reusable PaginationComponent */}
                    <PaginationComponent
                        apiEndpoint={`${BASE_URL}/api/sms-configurations`}
                        authToken={authToken}
                        onDataFetched={setSmsProviderListingData}
                        filters={appliedFilters} // Use applied filters
                        search={searchQuery} // Use live search
                        onPageChange={handlePageChange}
                        perPageValue={handlePerPageValue}
                        setLoading={setLoading}
                    />
                </CCol>
            </CRow>
        </>
    )
}

export default SmsProvider