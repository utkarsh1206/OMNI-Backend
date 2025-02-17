import React from 'react';
import { CTable, CSpinner, CTableHead, CTableRow, CTableHeaderCell, CTableDataCell, CTableBody } from '@coreui/react';

const LoadingTable = ({ loading, children, tableHeaders, dataLength }) => {
    return (
        <div className={loading ? 'table-wrapper-loading' : 'table-wrapper'}>
            {/* Display loading spinner */}
            {loading && (
                <div className="table-loading-spinner">
                    <CSpinner />
                </div>
            )}

            {/* Table content passed as children */}
            <CTable hover bordered responsive="md" className={loading ? 'table-loading' : ''}>
                <CTableHead color="light">
                    <CTableRow>
                        {
                            tableHeaders.map((h, i) => <CTableHeaderCell key={i}>{h}</CTableHeaderCell>)
                        }
                    </CTableRow>
                </CTableHead>

                <CTableBody>
                    {
                        dataLength === 0
                            ? (
                                <CTableRow>
                                    <CTableDataCell colSpan={7} className="text-center">
                                        No data found
                                    </CTableDataCell>
                                </CTableRow>
                            )
                            : children
                    }
                </CTableBody>
            </CTable>
        </div>
    );
};

export default LoadingTable;