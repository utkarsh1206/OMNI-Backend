import React, { useContext, useState } from 'react'
import { CButton, CCol, CContainer, CRow, CTableDataCell, CTableRow } from '@coreui/react'
import { Link, useNavigate } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'
import SmsProviderFilters from './SmsProviderFilters'
import { LoadingTable, SearchAndFilter, PaginationComponent, FormControlIcons } from '../../components'
import axiosInstance from '../../utils/axiosInstance'
import { useToast } from '../../context/ToastContext'
import { getErrorMessage } from '../../utils/errorHandlerUtils'

const SmsProvider = () => {
    const navigate = useNavigate();
    const { authToken } = useContext(AuthContext);
    const showToast = useToast();

    const [providers, setProviders] = useState([]);
    const [searchQuery, setSearchQuery] = useState('');
    const [loading, setLoading] = useState(false);
    const [filters, setFilters] = useState({ provider_name: '', method: '', enabled: '' });
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage, setPerPage] = useState(10);
    const [appliedFilters, setAppliedFilters] = useState({});

    const handleResetFilters = () => {
        setFilters({})
        setAppliedFilters({})
        setSearchQuery('')
    }

    const handleApplyFilters = (newFilters) => setAppliedFilters(newFilters)
    const handlePageChange = (page) => setCurrentPage(page)
    const handlePerPageValue = (perPage) => setPerPage(perPage)
    const renderProviderFilters = () => {
        return (
            <SmsProviderFilters filters={filters} setFilters={setFilters} />
        )
    }

    const handleDeleteProvider = async (id, apiName) => {
        if (window.confirm('Are you sure to delete ' + apiName + '?')) {
            try {
                await axiosInstance.delete(`/sms-configurations/${id}`);
                showToast('SMS provider configuration deleted successfully!', 'success');
                setProviders((prev) => prev.filter((provider) => provider.id !== id));
            } catch (error) {
                showToast(getErrorMessage(error), 'danger');
            }
        }
    }

    const setSmsProviderListingData = (data) => {
        const formatedData = [];
        for (const i of data) {
            if (!i?.configurations?.length) continue;
            const data = { provider_name: i.provider_name, id: i.provider_id };
            for (const j of i.configurations) {
                data[j.config_key] = j.config_value
            }
            formatedData.push(data);
        }
        setProviders(formatedData);
    }

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
                                onSearch={setSearchQuery}
                                onApplyFilters={handleApplyFilters}
                                onResetFilters={handleResetFilters}
                                filters={filters}
                                setFilters={setFilters}
                                renderFilters={renderProviderFilters}
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
                                <CTableDataCell>{index + 1 + (currentPage - 1) * perPage}</CTableDataCell>
                                <CTableDataCell>{provider.provider_name}</CTableDataCell>
                                <CTableDataCell>{provider.method}</CTableDataCell>
                                <CTableDataCell>{provider.type}</CTableDataCell>
                                <CTableDataCell>{provider.url}</CTableDataCell>
                                <CTableDataCell>{provider.max_length}</CTableDataCell>
                                <CTableDataCell>
                                    <CRow className="justify-content-center mx-0">
                                        <FormControlIcons.EditIcon clickHandler={() => navigate(`/sms_provider/edit/${provider.id}`)} />
                                        <FormControlIcons.DeleteIcon clickHandler={() => handleDeleteProvider(provider.id, provider.provider_name)} />
                                    </CRow>
                                </CTableDataCell>
                            </CTableRow>
                        ))}
                    </LoadingTable>

                    <PaginationComponent
                        apiEndpoint={`${BASE_URL}/api/sms-configurations`}
                        authToken={authToken}
                        onDataFetched={setSmsProviderListingData}
                        filters={appliedFilters}
                        search={searchQuery}
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