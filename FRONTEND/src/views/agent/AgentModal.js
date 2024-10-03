import React from 'react'
import {
    CButton,
    CCol,
    CLink,
    CModal,
    CModalBody,
    CModalFooter,
    CModalHeader,
    CModalTitle,
    CRow,
    CTable,
    CTableBody,
    CTableDataCell,
    CTableHead,
    CTableHeaderCell,
    CTableRow,
    CTooltip,
} from '@coreui/react'

const colorMap = {
    ready: 'info',
    incall: 'danger',
    paused: 'warning',
};

const AgentModal = ({ visible, setVisible }) => {

    const linkStyle = {
        textDecoration: 'none',
        color: 'inherit',
    }

    const renderContent = () => {
        return (
            <>
                <CModalHeader>
                    <CModalTitle id="OtherAgentsStatus">Other Agents Status</CModalTitle>
                </CModalHeader>
                <CModalBody>
                    <CRow className='mb-3 mx-auto'>
                        <CCol className='d-flex justify-content-center bg-info-subtle mx-4 text-dark'>
                            Ready
                        </CCol>
                        <CCol className='d-flex justify-content-center bg-danger-subtle mx-4 text-dark'>
                            InCall
                        </CCol>
                        <CCol className='d-flex justify-content-center bg-warning-subtle mx-4 text-dark'>
                            Paused
                        </CCol>
                    </CRow>
                    <CRow>
                        <CCol>
                            <CTable bordered>
                                <CTableHead color='primary'>
                                    <CTableRow>
                                        <CTableHeaderCell colSpan={2}>Agent Status Details</CTableHeaderCell>
                                    </CTableRow>
                                </CTableHead>
                                <CTableBody color='warning'>
                                    <CTableRow>
                                        <CTableDataCell>
                                            <CTooltip content="Agent ID">
                                                <CLink style={linkStyle}>Agent ID</CLink>
                                            </CTooltip>
                                        </CTableDataCell>
                                        <CTableDataCell>
                                            <CTooltip content="Agent Name" placement="bottom">
                                                <CLink style={linkStyle}>Agent Name</CLink>
                                            </CTooltip>
                                        </CTableDataCell>
                                    </CTableRow>
                                    <CTableRow>
                                        <CTableDataCell color={colorMap.incall}>
                                            Test Value
                                        </CTableDataCell>
                                        <CTableDataCell color={colorMap.ready}>
                                            Test Value 2
                                        </CTableDataCell>
                                    </CTableRow>
                                </CTableBody>
                            </CTable>
                        </CCol>
                    </CRow>
                </CModalBody >
                <CModalFooter>
                    {/* <CButton type="submit" color="primary" form="userForm">Submit</CButton> */}
                    <CButton color="secondary" onClick={() => setVisible(false)}>
                        Close
                    </CButton>
                </CModalFooter>
            </>
        )

    }

    return (
        <CModal
            scrollable
            alignment='center'
            size="lg"
            // backdrop="static"
            visible={visible}
            onClose={() => setVisible(false)}
            aria-labelledby="OtherAgentsStatus"
            className="responsive-modal"
        >
            {renderContent()}       {/* Could have directly used the contents of the function here */}
        </CModal>
    )
}

export default AgentModal
