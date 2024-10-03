import React from 'react'
import { CTable, CTableBody, CTableDataCell, CTableHead, CTableHeaderCell, CTableRow } from '@coreui/react'

export default function CallsTodayTable() {
    return (
        <CTable bordered >
            <CTableHead className='text-center' color='primary'>
                <CTableRow>
                    <CTableHeaderCell scope="col" colSpan={3}>Calls Today : 0</CTableHeaderCell>
                </CTableRow>
                <CTableRow>
                    <CTableHeaderCell scope="col"></CTableHeaderCell>
                    <CTableHeaderCell scope="col">Connected</CTableHeaderCell>
                    <CTableHeaderCell scope="col">Abandoned</CTableHeaderCell>
                </CTableRow>
            </CTableHead>
            <CTableBody color='info'>
                <CTableRow >
                    <CTableDataCell>Inbound</CTableDataCell>
                    <CTableDataCell>0</CTableDataCell>
                    <CTableDataCell>0</CTableDataCell>
                </CTableRow>
                <CTableRow>
                    <CTableDataCell>Outbound</CTableDataCell>
                    <CTableDataCell>0</CTableDataCell>
                    <CTableDataCell>0</CTableDataCell>
                </CTableRow>
                <CTableRow>
                    <CTableDataCell>Total</CTableDataCell>
                    <CTableDataCell>0</CTableDataCell>
                    <CTableDataCell>0</CTableDataCell>
                </CTableRow>
            </CTableBody>
        </CTable>
    )
}
