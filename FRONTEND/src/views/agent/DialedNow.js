import { CCard, CCardBody, CCardHeader, CCardTitle } from '@coreui/react'
import React from 'react'
import InOutCards from './InOutCards'

export default function DialedNow() {
    return (
        <CCard>
            <CCardHeader>
                <CCardTitle>
                    Dialed Now
                </CCardTitle>
            </CCardHeader>
            <CCardBody>
                <InOutCards />
            </CCardBody>
        </CCard>
    )
}
