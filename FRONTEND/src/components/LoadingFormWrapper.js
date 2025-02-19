import { CSpinner } from "@coreui/react";

const LoadingFormWrapper = ({ children, loading }) => {
    return (
        <div className='table-wrapper-loading'>
            {loading && (
                <div className="table-loading-spinner">
                    <CSpinner />
                </div>
            )}

            {children}
        </div>
    )
}

export default LoadingFormWrapper;