import React from 'react'
import { CTable, CTableBody, CTableDataCell, CTableHead, CTableHeaderCell, CTableRow } from '@coreui/react'

export default function StatusTable() {
    return (
        <CTable bordered>
            <CTableHead className='text-center' color='primary'>
                <CTableRow>
                    <CTableHeaderCell scope="col" colSpan={2}>Status</CTableHeaderCell>
                </CTableRow>
            </CTableHead>
            <CTableBody color='info'>
                <CTableRow className='text-center'>
                    <CTableDataCell colSpan={2}>Calls in Queue: 0</CTableDataCell>
                </CTableRow>
                <CTableRow>
                    <CTableDataCell><span style={{ color: 'red' }}>Unique ID :</span> No Active Calls</CTableDataCell>
                </CTableRow>
            </CTableBody>
        </CTable>
    )
}
