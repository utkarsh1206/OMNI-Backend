import React, { useEffect, useState } from 'react'
import { CTable, CTableBody, CTableDataCell, CTableHead, CTableHeaderCell, CTableRow } from '@coreui/react'

export default function CustomerTimeTable() {
    const [date, setDate] = useState(new Date())
    useEffect(() => {
        var timer = setInterval(() => setDate(new Date()), 1000)
        return function cleanup() {
            clearInterval(timer)
        }
    })

    return (
        <CTable bordered >
            <CTableHead className='text-center' color='primary'>
                <CTableRow>
                    <CTableHeaderCell scope="col" colSpan={2}>Customer Time</CTableHeaderCell>
                </CTableRow>
            </CTableHead>
            <CTableBody color='info'>
                <CTableRow>
                    <CTableDataCell>Customer Time</CTableDataCell>
                    <CTableDataCell></CTableDataCell>
                </CTableRow>
                <CTableRow>
                    <CTableDataCell>Date and Time</CTableDataCell>
                    <CTableDataCell>{date.toLocaleString()}</CTableDataCell>
                </CTableRow>
            </CTableBody>
        </CTable>
    )
}
