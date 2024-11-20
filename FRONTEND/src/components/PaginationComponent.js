import React, { useEffect, useState } from 'react'
import { CRow, CCol, CPagination, CPaginationItem, CFormSelect } from '@coreui/react'
import axios from 'axios'

const PaginationComponent = ({ apiEndpoint, authToken, onDataFetched, filters = {}, search }) => {
  // Pagination state
  const [currentPage, setCurrentPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [total, setTotal] = useState(0)
  const [perPage, setPerPage] = useState(10)

  // Fetch data from API
  const fetchData = (page = 1) => {
    // testing
    const source = axios.CancelToken.source() // Create a cancel token

    // Cancel the previous request when a new one is made
    if (fetchData.cancelToken) {
      fetchData.cancelToken.cancel('Operation canceled due to new request.')
    }
    fetchData.cancelToken = source // Store the cancel token for future use
    // testing end

    axios
      .get(apiEndpoint, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authToken}`,
        },
        params: {
          page: page,
          per_page: perPage,
          ...filters,
          search,
        },
        cancelToken: source.token, // Attach cancel token to the request
      })
      .then((response) => {
        const data = response.data.data
        onDataFetched(data) // Pass data back to parent component
        setCurrentPage(response.data.meta.current_page)
        setLastPage(response.data.meta.last_page)
        setTotal(response.data.meta.total)
        setPerPage(response.data.meta.per_page)
      })
      // Original
      // .catch((error) => {
      //   console.error('There was an error fetching the data!', error)
      // })
      // Original

      .catch((error) => {
        if (axios.isCancel(error)) {
          console.log('Request canceled:', error.message)
        } else {
          console.error('There was an error fetching the data!', error)
        }
      })
  }

  useEffect(() => {
    fetchData()
  }, [filters, search, perPage])

  const handlePageChange = (page) => {
    fetchData(page)
  }

  // Function to generate pagination items with ellipses
  const getPaginationItems = () => {
    const pages = []
    const maxVisiblePages = 5

    if (lastPage <= maxVisiblePages) {
      // Show all pages if total pages are less than or equal to maxVisiblePages
      for (let page = 1; page <= lastPage; page++) {
        pages.push(
          <CPaginationItem
            key={page}
            active={page === currentPage}
            onClick={() => handlePageChange(page)}
          >
            {page}
          </CPaginationItem>,
        )
      }
    } else {
      // Show limited number of pages with ellipses
      let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2))
      let endPage = Math.min(lastPage, startPage + maxVisiblePages - 1)

      if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1)
      }

      if (startPage > 1) {
        pages.push(
          <CPaginationItem key={1} onClick={() => handlePageChange(1)}>
            1
          </CPaginationItem>,
        )
        if (startPage > 2) {
          pages.push(
            <CPaginationItem key="start-ellipsis" disabled>
              ...
            </CPaginationItem>,
          )
        }
      }

      for (let page = startPage; page <= endPage; page++) {
        pages.push(
          <CPaginationItem
            key={page}
            active={page === currentPage}
            onClick={() => handlePageChange(page)}
          >
            {page}
          </CPaginationItem>,
        )
      }

      if (endPage < lastPage) {
        if (endPage < lastPage - 1) {
          pages.push(
            <CPaginationItem key="end-ellipsis" disabled>
              ...
            </CPaginationItem>,
          )
        }
        pages.push(
          <CPaginationItem key={lastPage} onClick={() => handlePageChange(lastPage)}>
            {lastPage}
          </CPaginationItem>,
        )
      }
    }

    return pages
  }

  return (
    <>
      {/* Pagination Controls */}
      <CRow className="mt-3">
        <CCol className="d-flex justify-content-start">
          <CPagination aria-label="Page navigation">
            <CPaginationItem
              disabled={currentPage === 1}
              onClick={() => handlePageChange(currentPage - 1)}
            >
              Previous
            </CPaginationItem>
            {/* Display page numbers with ellipses */}
            {getPaginationItems()}
            <CPaginationItem
              disabled={currentPage === lastPage}
              onClick={() => handlePageChange(currentPage + 1)}
            >
              Next
            </CPaginationItem>
          </CPagination>
        </CCol>
        <CCol className="d-flex justify-content-end align-items-baseline">
          <span className="me-2">PerPage Display:</span>
          <CFormSelect
            id="perPage"
            name="perPage"
            value={perPage}
            onChange={(e) => setPerPage(e.target.value)}
            className="me-2"
            size="sm"
            style={{ width: '80px' }}
          >
            <option key="5" value="5">
              5
            </option>
            <option key="10" value="10">
              10
            </option>
            <option key="15" value="15">
              15
            </option>
            <option key="20" value="20">
              20
            </option>
            <option key="50" value="50">
              50
            </option>
            <option key="100" value="100">
              100
            </option>
          </CFormSelect>
        </CCol>
      </CRow>

      {/* Display total items and current range */}
      <CRow className="mb-2">
        <CCol className="text-center">
          Showing {(currentPage - 1) * perPage + 1} to {Math.min(currentPage * perPage, total)} of{' '}
          {total}
        </CCol>
      </CRow>
    </>
  )
}

export default PaginationComponent
