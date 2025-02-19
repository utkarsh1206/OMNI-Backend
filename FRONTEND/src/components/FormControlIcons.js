import { CCol, CButton } from "@coreui/react";
import CIcon from "@coreui/icons-react";
import { cilPencil, cilXCircle } from "@coreui/icons";

const EditIcon = ({ clickHandler }) => {
    return (
        <CCol className="mx-auto d-flex justify-content-center">
            <CButton
                color="warning"
                size="sm"
                onClick={clickHandler}
            >
                <CIcon icon={cilPencil} />
            </CButton>
        </CCol>
    )
}

const DeleteIcon = ({ clickHandler }) => {
    return (
        <CCol className="mx-auto d-flex justify-content-center">
            <CButton
                color="danger"
                size="sm"
                onClick={clickHandler}
            >
                <CIcon icon={cilXCircle} />
            </CButton>
        </CCol>
    )
}

export default {
    EditIcon,
    DeleteIcon
}