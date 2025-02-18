import { CCol, CTooltip, CButton } from "@coreui/react";
import CIcon from "@coreui/icons-react";
import { cilPencil, cilXCircle } from "@coreui/icons";

const EditIcon = ({ clickHandler }) => {
    return (
        <CCol className="mx-auto d-flex justify-content-center">
            <CTooltip content="Edit">
                <CButton
                    color="warning"
                    size="sm"
                    onClick={clickHandler}
                >
                    <CIcon icon={cilPencil} />
                </CButton>
            </CTooltip>
        </CCol>
    )
}

const DeleteIcon = ({ clickHandler }) => {
    return (
        <CCol className="mx-auto d-flex justify-content-center">
            <CTooltip content="Delete">
                <CButton
                    color="danger"
                    size="sm"
                    onClick={clickHandler}
                >
                    <CIcon icon={cilXCircle} />
                </CButton>
            </CTooltip>
        </CCol>
    )
}

export default {
    EditIcon,
    DeleteIcon
}