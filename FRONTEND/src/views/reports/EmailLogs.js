import React, { useState, useEffect, useContext } from 'react';
import {
    CButton,
    CCol,
    CContainer,
    CRow,
    CTable,
    CTableBody,
    CTableDataCell,
    CTableHead,
    CTableHeaderCell,
    CTableRow,
    CFormSelect,
    CFormLabel,
    CFormInput,
} from '@coreui/react';
import { BASE_URL } from '../../config';
import { cilPhone, cilEnvelopeOpen, cilContact } from '@coreui/icons';
import CIcon from '@coreui/icons-react';
import { AuthContext } from '../../context/AuthContext';
import PaginationComponent from '../../components/PaginationComponent';
import axios from 'axios';
import SearchAndFilter from '../../components/SearchAndFilter';

const EmailLogs = () => {
    const [emailLogs, setEmailLogs] = useState([]);
    const { authToken } = useContext(AuthContext);
    const [searchQuery, setSearchQuery] = useState('');
    const [filters, setFilters] = useState({
        status: '',
        user_id: '',
        date: '',  // Single date filter
    });
    const [currentPage, setCurrentPage] = useState(1);
    const [perPage, setPerPage] = useState(10);
    const [appliedFilters, setAppliedFilters] = useState({});
    const [users, setUsers] = useState([]);  // Users will now come from the email logs

    const handleApplyFilters = () => {
        setAppliedFilters(filters);  // Apply the current filter settings
        setCurrentPage(1);  // Reset to first page when filters are applied
    };

    const handleResetFilters = () => {
        setFilters({ status: '', user_id: '', date: '' });
        setAppliedFilters({});
        setSearchQuery('');
    };

    const handlePageChange = (page) => {
        setCurrentPage(page);
    };

    const handlePerPageValue = (perPage) => {
        setPerPage(perPage);
    };

    const [isLoading, setIsLoading] = useState(false); // Add loading state

    // Fetch users for the dropdown (from the new endpoint with all_user=Y)
    useEffect(() => {
        const fetchUsers = async () => {
            try {
                const response = await axios.get(`${BASE_URL}/api/users?all_user=Y`, {
                    headers: {
                        Authorization: `Bearer ${authToken}`,
                    },
                });
                setUsers(response.data.data);  // Set users for the dropdown (from users API)
            } catch (err) {
                console.error('Error fetching users', err);
            }
        };
        fetchUsers();
    }, [authToken]);

    // Fetch email logs with pagination and filters
    useEffect(() => {
        const fetchEmailLogs = async () => {
            try {
                const response = await axios.get(`${BASE_URL}/api/email-logs`, {
                    headers: {
                        Authorization: `Bearer ${authToken}`,
                    },
                    params: {
                        search: searchQuery,
                        per_page: perPage,
                        page: currentPage,  // Ensure currentPage is passed here
                        ...appliedFilters,  // Only apply filters after Apply button is clicked
                    },
                });
                setEmailLogs(response.data.data);
            } catch (err) {
                console.error('Error fetching email logs', err);
            }
        };

        if (Object.keys(appliedFilters).length > 0 || searchQuery !== '') {
            fetchEmailLogs();  // Fetch only if filters or search query are applied
        }
    }, [authToken, searchQuery, perPage, currentPage, appliedFilters]);

    // Helper to show user contact details
    const getContactInfo = (user) => {
        return (
            <>
                <CIcon icon={cilContact} /> {user.username} <br />
                <CIcon icon={cilEnvelopeOpen} /> {user.email} <br />
                <CIcon icon={cilPhone} /> {user.mobile}
            </>
        );
    };

    // Helper to show formatted date
    const getDate = (log) => {
        const formattedDate = new Date(log.created_at).toLocaleString();
        return <>{formattedDate}</>;
    };

    // Helper for status
    const getStatusIcon = (status) => {
        return status === 'sent' ? (
            <span style={{ color: 'green' }}>Sent</span>
        ) : (
            <span style={{ color: 'red' }}>Fail</span>
        );
    };

    // Handle CSV Export
    const handleExportCSV = async () => {
        try {
            setIsLoading(true);

            // Constructing the filters (same as in the backend)
            const userFilter = filters.user_id || 'all';  // 'all' if no user selected
            const startDateStr = filters.date ? new Date(filters.date).toISOString() : '';  // If a date is set
            const statusFilter = filters.status || 'all';  // 'all' if no status selected

            // Fetching the CSV with applied filters
            const response = await axios.get(`${BASE_URL}/api/email_logs/csv`, {
                headers: {
                    Authorization: `Bearer ${authToken}`,
                },
                params: {
                    search: searchQuery,
                    user_id: userFilter !== 'all' ? userFilter : undefined,
                    status: statusFilter !== 'all' ? statusFilter : undefined,
                    date: startDateStr, // Single date filter
                    page: 'all', // Fetch all records (no pagination)
                },
                nseType: 'blob', // Important for CSV file handling
            });

            // Create a Blob from the response data
            const blob = new Blob([response.data], { type: 'text/csv' });

            // Create a link element to trigger the download
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `email_logs_${new Date().toISOString()}.csv`);

            // Append link to the document body and trigger click event to download
            document.body.appendChild(link);
            link.click();

            // Clean up and revoke URL
            window.URL.revokeObjectURL(url);
            document.body.removeChild(link);

            alert('CSV file has been generated and is ready for download.');
        } catch (error) {
            console.error('Error exporting CSV:', error);
            alert('An error occurred while generating the CSV file. Please try again.');
        } finally {
            setIsLoading(false);
        }
    };


    return (
        <CRow>
            <CCol>
                <CContainer>
                    <CRow className="mb-3 align-items-center">
                        <CCol xs="auto" className="mb-2">
                            <CButton
                                size="sm"
                                color="primary"
                                onClick={handleExportCSV}  // Trigger CSV download
                            >
                                Export CSV
                            </CButton>
                        </CCol>
                        <SearchAndFilter
                            onSearch={setSearchQuery}
                            onApplyFilters={handleApplyFilters}
                            onResetFilters={handleResetFilters}
                            filters={filters}
                            setFilters={setFilters}
                            renderFilters={(filters, setFilters) => (
                                <CRow>
                                    <CCol xs="auto" className="mb-2">
                                        <CFormLabel>Status</CFormLabel>
                                        <CFormSelect
                                            value={filters.status}
                                            onChange={(e) => setFilters({ ...filters, status: e.target.value })}
                                        >
                                            <option value="">All</option>
                                            <option value="sent">Sent</option>
                                            <option value="fail">Failed</option>
                                        </CFormSelect>
                                    </CCol>
                                    <CCol xs="auto" className="mb-2">
                                        <CFormLabel>User</CFormLabel>
                                        <CFormSelect
                                            value={filters.user_id}
                                            onChange={(e) => setFilters({ ...filters, user_id: e.target.value })}
                                        >
                                            <option value="">All</option>
                                            {users.map((user) => (
                                                <option key={user.id} value={user.id}>
                                                    {user.username}
                                                </option>
                                            ))}
                                        </CFormSelect>
                                    </CCol>
                                    <CCol xs="auto" className="mb-2">
                                        <CFormLabel>Date</CFormLabel>
                                        <CFormInput
                                            type="date"
                                            value={filters.date}
                                            onChange={(e) => setFilters({ ...filters, date: e.target.value })}
                                        />
                                    </CCol>
                                </CRow>
                            )}
                        />
                    </CRow>
                </CContainer>
                <hr />
                <CTable hover bordered responsive="md">
                    <CTableHead color="light">
                        <CTableRow>
                            <CTableHeaderCell scope="col">#</CTableHeaderCell>
                            <CTableHeaderCell scope="col">Contact User</CTableHeaderCell>
                            <CTableHeaderCell scope="col">Email Subject</CTableHeaderCell>
                            <CTableHeaderCell scope="col">From Mail</CTableHeaderCell>
                            <CTableHeaderCell scope="col">To Mail</CTableHeaderCell>
                            <CTableHeaderCell scope="col">Date & Time</CTableHeaderCell>
                            <CTableHeaderCell scope="col">Status</CTableHeaderCell>
                        </CTableRow>
                    </CTableHead>
                    <CTableBody>
                        {emailLogs.map((log, index) => (
                            <CTableRow key={log.id}>
                                <CTableDataCell>{(currentPage - 1) * perPage + (index + 1)}</CTableDataCell>
                                <CTableDataCell>{getContactInfo(log.user)}</CTableDataCell>
                                <CTableDataCell>{log.email_template.subject}</CTableDataCell>
                                <CTableDataCell>{log.from_email}</CTableDataCell>
                                <CTableDataCell>{log.user.email}</CTableDataCell>
                                <CTableDataCell>{getDate(log)}</CTableDataCell>
                                <CTableDataCell>{getStatusIcon(log.status)}</CTableDataCell>
                            </CTableRow>
                        ))}
                    </CTableBody>
                </CTable>

                <PaginationComponent
                    apiEndpoint={`${BASE_URL}/api/email-logs`}
                    authToken={authToken}
                    onDataFetched={setEmailLogs}
                    filters={appliedFilters}
                    search={searchQuery}
                    onPageChange={handlePageChange}
                    perPageValue={handlePerPageValue}
                />
            </CCol>
        </CRow>
    );
};

export default EmailLogs;
