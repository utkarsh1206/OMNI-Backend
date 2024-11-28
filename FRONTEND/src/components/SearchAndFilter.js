import React, { useState, useEffect } from 'react'
import { CButton, CCard, CCardBody, CCardHeader, CCol, CFormInput, CRow } from '@coreui/react'
import CIcon from '@coreui/icons-react'
import { cilFilter } from '@coreui/icons'

const SearchAndFilter = ({ onSearch, filters, setFilters, renderFilters }) => {
  const [liveSearch, setLiveSearch] = useState('')
  const [isFilterVisible, setIsFilterVisible] = useState(false)

  // Debounce live search updates
  useEffect(() => {
    const timer = setTimeout(() => {
      onSearch(liveSearch) // Pass the search query to the parent
    }, 500)

    return () => clearTimeout(timer)
  }, [liveSearch, onSearch])

  const toggleFilterVisibility = () => {
    setIsFilterVisible((prev) => !prev)
  }

  return (
    <>
      {/* <CRow className="d-flex justify-content-end mb-3 border border-3"> */}
      <CCol xs="auto" className='ms-auto'>
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
      {/* </CRow> */}
      {isFilterVisible && (
        <CCol xs={12} className="mt-3">
          <CCard className="mb-1">
            <CCardHeader className="d-flex justify-content-between align-items-center">
              <div>
                <strong>Filters</strong>
              </div>
            </CCardHeader>
            <CCardBody>
              {/* Render custom filters */}
              {renderFilters ? renderFilters(filters, setFilters) : null}
            </CCardBody>
          </CCard>
        </CCol>
      )}
    </>
  )
}

export default SearchAndFilter
