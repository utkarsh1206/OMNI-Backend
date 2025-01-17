import React, { useState, useEffect } from 'react'
import { CButton, CCard, CCardBody, CCardHeader, CCol, CFormInput, CRow } from '@coreui/react'
import CIcon from '@coreui/icons-react'
import { cilFilter } from '@coreui/icons'

const SearchAndFilter = ({
  onSearch,
  onApplyFilters,
  onResetFilters,
  filters,
  setFilters,
  renderFilters,
}) => {
  const [liveSearch, setLiveSearch] = useState('')
  const [isFilterVisible, setIsFilterVisible] = useState(false)

  // Debounce live search updates
  useEffect(() => {
    const timer = setTimeout(() => {
      onSearch(liveSearch) // Trigger the live search callback
    }, 500)

    return () => clearTimeout(timer)
  }, [liveSearch, onSearch])

  const toggleFilterVisibility = () => {
    setIsFilterVisible((prev) => !prev)
  }

  const handleApplyFilters = () => {
    onApplyFilters(filters) // Trigger the apply filters callback
  }

  const handleResetFilters = () => {
    setFilters({}) // Clear the filters in the UI
    setLiveSearch('') // Clear the live search input
    onResetFilters() // Trigger the reset filters callback
  }

  return (
    <>
      <CCol xs="auto" className="ms-auto">
        <div className="d-flex align-items-center">
          <span className="me-2">Search:</span>
          <CFormInput
            type="text"
            id="search"
            name="search"
            value={liveSearch}
            onChange={(e) => setLiveSearch(e.target.value)}
            size="sm"
          />
        </div>
      </CCol>
      <CCol xs="auto">
        <CButton color="success" size="sm" onClick={toggleFilterVisibility}>
          <CIcon icon={cilFilter} />
        </CButton>
      </CCol>
      {isFilterVisible && (
        <CCol xs={12} className="mt-3">
          <CCard className="mb-1">
            <CCardHeader className="d-flex justify-content-between align-items-center">
              <div>
                <strong>Filters</strong>
              </div>
            </CCardHeader>
            <CCardBody>
              {renderFilters ? renderFilters(filters, setFilters) : null}
              <CRow className="justify-content-end">
                {/* <CCol xs="auto" className="mt-3"> */}
                <CCol xs="auto" className="mt-1">
                  <CButton color="primary" onClick={handleApplyFilters}>
                    Apply Filters
                  </CButton>
                </CCol>
                <CCol xs="auto" className="mt-1">
                  <CButton color="secondary" onClick={handleResetFilters}>
                    Reset
                  </CButton>
                </CCol>
              </CRow>
            </CCardBody>
          </CCard>
        </CCol>
      )}
    </>
  )
}

export default SearchAndFilter
