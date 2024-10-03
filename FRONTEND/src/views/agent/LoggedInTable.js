import React from 'react'
import { CTable, CTableBody, CTableDataCell, CTableHead, CTableHeaderCell, CTableRow } from '@coreui/react'

export default function LoggedInTable() {
    return (
        <CTable bordered>
            <CTableHead className='text-center' color='primary'>
                <CTableRow>
                    <CTableHeaderCell scope="col" colSpan={2}>Logged In Details</CTableHeaderCell>
                </CTableRow>
            </CTableHead>
            <CTableBody color='info'>
                <CTableRow >
                    <CTableDataCell >User</CTableDataCell>
                    <CTableDataCell >UserName</CTableDataCell>
                </CTableRow>
                <CTableRow>
                    <CTableDataCell >Phone</CTableDataCell>
                    <CTableDataCell >Phone Number</CTableDataCell>
                </CTableRow>
                <CTableRow>
                    <CTableDataCell >Campaign</CTableDataCell>
                    <CTableDataCell >Campaign Name</CTableDataCell>
                </CTableRow>
            </CTableBody>
        </CTable>
    )
}
