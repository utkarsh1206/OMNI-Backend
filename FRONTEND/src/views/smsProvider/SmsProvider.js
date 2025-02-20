import React, { useContext, useState, useCallback } from 'react'
import { CButton, CCol, CContainer, CRow, CTableDataCell, CTableRow } from '@coreui/react'
import { Link, useNavigate } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'
import SmsProviderFilters from './SmsProviderFilters'
import { LoadingTable, SearchAndFilter, PaginationComponent, FormControlIcons, DeleteConfirmationModal } from '../../components'
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
    const [deleteRecordData, setDeleteRecordData] = useState({});
    const [openDeleteModal, setOpenDeleteModal] = useState(false);

    const handleResetFilters = useCallback(() => {
        setFilters({})
        setAppliedFilters({})
        setSearchQuery('')
    }, []);

    const handleApplyFilters = useCallback((newFilters) => setAppliedFilters(newFilters), []);
    const handlePageChange = useCallback((page) => setCurrentPage(page), []);
    const handlePerPageValue = useCallback((perPage) => setPerPage(perPage), []);

    const renderProviderFilters = () => {
        return (
            <SmsProviderFilters filters={filters} setFilters={setFilters} />
        )
    };

    const handleDeleteProvider = async (id) => {
        try {
            await axiosInstance.delete(`/sms-configurations/${id}`);
            showToast('SMS provider configuration deleted successfully!', 'success');
            setProviders((prev) => prev.filter((provider) => provider.id !== id));
        } catch (error) {
            showToast(getErrorMessage(error), 'danger');
        } finally {
            setOpenDeleteModal(false);
        }
    }

    const setSmsProviderListingData = useCallback((data) => {
        const formatedData = data.reduce((acc, i) => {
            if (!i?.configurations?.length) return acc;
            const providerData = { provider_name: i.provider_name, id: i.provider_id };
            i.configurations.forEach(j => {
                providerData[j.config_key] = j.config_value;
            });
            acc.push(providerData);
            return acc;
        }, []);
        setProviders(formatedData);
    }, []);

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
                                        <FormControlIcons.DeleteIcon clickHandler={() => {
                                            setDeleteRecordData({
                                                id: provider.id,
                                                name: provider.provider_name
                                            });
                                            setOpenDeleteModal(true);
                                        }} />
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

            {/* Delete Modal */}
            <DeleteConfirmationModal
                visible={openDeleteModal}
                setVisible={setOpenDeleteModal}
                dataName={deleteRecordData.name}
                handleDelete={() => handleDeleteProvider(deleteRecordData.id)}
            />
        </>
    )
}

export default SmsProvider