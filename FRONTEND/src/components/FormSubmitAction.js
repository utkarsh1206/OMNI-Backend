import { CRow, CCol, CButton, CSpinner } from "@coreui/react";

const FormSubmitAction = ({ handleCancel, isSubmitting, isEditMode }) => {
    return (
        <CRow className="mt-2 mb-4">
            <CCol xs="12" className="d-flex justify-content-end">
                <CButton type="submit" color="primary" className="me-2" disabled={isSubmitting}>
                    {isSubmitting ? (
                        <>
                            <CSpinner size="sm" className="me-2" />
                            {isEditMode ? "Updating..." : "Submitting..."}
                        </>
                    ) : isEditMode ? (
                        "Update"
                    ) : (
                        "Submit"
                    )}
                </CButton>
                <CButton type="button" color="secondary" onClick={handleCancel} disabled={isSubmitting}>
                    Cancel
                </CButton>
            </CCol>
        </CRow>
    )
}

export default FormSubmitAction;