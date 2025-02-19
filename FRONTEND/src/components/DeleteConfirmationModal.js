import React from 'react';
import { CButton, CModal, CModalBody, CModalFooter, CModalHeader, CModalTitle } from '@coreui/react';

const DeleteConfirmationModal = React.memo(({ visible, setVisible, dataName, handleDelete }) => {
    return (
        <CModal
            backdrop="static"
            visible={visible}
            onClose={() => setVisible(false)}
            aria-labelledby="DeleteConfirmationModalLabel"
        >
            <CModalHeader>
                <CModalTitle id="DeleteConfirmationModalLabel">Confirm Deletion</CModalTitle>
            </CModalHeader>
            <CModalBody>
                {`Are you sure you want to delete ${dataName}?`}
            </CModalBody>
            <CModalFooter>
                <CButton color="secondary" onClick={() => setVisible(false)}>
                    Cancel
                </CButton>
                <CButton color="danger" onClick={handleDelete}>
                    Delete
                </CButton>
            </CModalFooter>
        </CModal>
    )
});

export default DeleteConfirmationModal;