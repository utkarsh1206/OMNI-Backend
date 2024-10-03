import React, { useState } from 'react';
import { Stack, Button, Box, Fab, Paper } from '@mui/material';
import { cilAlarm, cilClearAll, cilLockUnlocked, cilPhone, cilUser, cilX } from '@coreui/icons';
import CIcon from '@coreui/icons-react';
import { COffcanvas, COffcanvasHeader, COffcanvasBody, CCloseButton, CCol, CCard } from '@coreui/react';
import LoggedInTable from './LoggedInTable';
import CallsTodayTable from './CallsTodayTable';
import CustomerTimeTable from './CustomerTimeTable';
import StatusTable from './StatusTable';
import AgentModal from './AgentModal';

const contentMap = {
    callsToday: { header: 'Calls Today', component: <CallsTodayTable /> },
    customerTime: { header: 'Customer Time', component: <CustomerTimeTable /> },
    status: { header: 'Status', component: <StatusTable /> },
    loginDetails: { header: 'Logged-in Details', component: <LoggedInTable /> }
};

const statusMap = {
    ready: { color: 'error', textShort: "R", text: "READY", cardColor: 'danger' },
    paused: { color: 'warning', textShort: "P", text: "PAUSED", cardColor: 'warning' },
    disposition: { color: 'success', textShort: "D", text: "DISPOSITION", cardColor: 'success' },
}

const VerticalButtonGroup = () => {
    const [offcanvasOpen, setOffcanvasOpen] = useState(false);
    const [contentId, setContentId] = useState('');
    const [buttonsVisible, setButtonsVisible] = useState(false);
    const [agentVisible, setAgentVisible] = useState(false)

    const [liveStatus, setLiveStatus] = useState("disposition")

    const [hovered, setHovered] = useState(false);

    const handleButtonClick = (id) => {
        setContentId(id);
        setOffcanvasOpen(true);
    };

    const handleOffcanvasClose = () => {
        setOffcanvasOpen(false);
    };

    const renderContent = () => {
        const content = contentMap[contentId];
        return content ? content.component : null;
    };

    const renderHeader = () => {
        const content = contentMap[contentId];
        return content ? content.header : '';
    };

    const toggleButtonsVisibility = () => {
        setButtonsVisible(!buttonsVisible);
    };

    const handleAgentView = () => {
        setAgentVisible(true)
    }

    const buttonStyles = {
        boxShadow: '2px 4px 8px rgba(0, 0, 0, 0.8)',
        backgroundColor: '#1b47c5',
    };

    return (
        <>
            <Box
                sx={{
                    position: 'fixed',
                    right: 16,
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    gap: 2,
                }}
                style={{ bottom: 'calc(50% - 5rem)' }}
            >
                <Fab aria-label="talk-time" style={{ backgroundColor: '#f9b115', color: '#011303' }}>
                    <strong>00:00</strong>
                </Fab>
                <Fab color={statusMap[liveStatus].color} aria-label="status-timer" onMouseEnter={() => setHovered(true)} onMouseLeave={() => setHovered(false)}>
                    {/* <Fab color="success" aria-label="status-timer" onMouseEnter={() => setHovered(true)} onMouseLeave={() => setHovered(false)}> */}
                    <strong>{statusMap[liveStatus].textShort}</strong>
                </Fab>

                {hovered && (
                    <Paper
                        elevation={3}
                        sx={{
                            position: 'absolute',
                            top: '50%',
                            left: '-230px',
                            transform: 'translateY(-50%)',
                            padding: '5px',
                            width: '220px',
                            backgroundColor: "rgba(255, 255, 255, 0.1)"
                        }}
                    >

                        <CCard color={statusMap[liveStatus].cardColor} textBgColor={statusMap[liveStatus].cardColor}>
                            <div className=' border-bottom border-dark mx-auto d-flex justify-content-center'>
                                <strong>Status: {statusMap[liveStatus].text}</strong>
                            </div>
                            <div className='mx-auto d-flex justify-content-center fs-6'>
                                Timer: 00:00:00
                            </div>
                        </CCard>

                        {/* Hover content */}
                    </Paper>
                )}

                <Fab style={{ backgroundColor: '#1b47c5', color: 'white' }} aria-label="metrics" onClick={toggleButtonsVisibility}>
                    {buttonsVisible ?
                        // <CIcon icon={cilClearAll} /> :
                        <CIcon icon={cilX} /> :
                        <CIcon icon={cilClearAll} style={{ transform: 'scaleX(-1)' }} />}
                </Fab>
                {buttonsVisible && (
                    <Box sx={{ position: 'fixed', right: 90, transform: 'translateY(-20%)', display: 'flex', flexDirection: 'column', alignItems: 'flex-end' }}>
                        <Stack direction="column" spacing={2}>
                            <Button
                                variant="contained"
                                startIcon={<CIcon icon={cilPhone} />}
                                onClick={() => handleButtonClick('callsToday')}
                                sx={buttonStyles}
                            >
                                Calls Today
                            </Button>
                            <Button
                                variant="contained"
                                startIcon={<CIcon icon={cilAlarm} />}
                                onClick={() => handleButtonClick('customerTime')}
                                sx={buttonStyles}
                            >
                                Customer Time
                            </Button>
                            <Button
                                variant="contained"
                                startIcon={<CIcon icon={cilUser} />}
                                onClick={() => handleButtonClick('status')}
                                sx={buttonStyles}
                            >
                                Status
                            </Button>
                            <Button
                                variant="contained"
                                startIcon={<CIcon icon={cilLockUnlocked} />}
                                onClick={() => handleButtonClick('loginDetails')}
                                sx={buttonStyles}
                            >
                                Logged-in Details
                            </Button>
                            <Button
                                variant="contained"
                                onClick={handleAgentView}
                                sx={buttonStyles}
                            >
                                Agent View +
                            </Button>
                            <AgentModal visible={agentVisible} setVisible={setAgentVisible} />
                        </Stack>
                    </Box>
                )}
            </Box>
            <COffcanvas
                visible={offcanvasOpen}
                placement="end"
                onHide={handleOffcanvasClose}
            >
                <COffcanvasHeader>
                    <CCol xs={10}>
                        <h5>{renderHeader()}</h5>
                    </CCol>
                    <CCol xs={2}>
                        <CCloseButton className="text-reset" onClick={handleOffcanvasClose} />
                    </CCol>
                </COffcanvasHeader>
                <COffcanvasBody>
                    {renderContent()}
                </COffcanvasBody>
            </COffcanvas>
        </>
    );
};

export default VerticalButtonGroup;
