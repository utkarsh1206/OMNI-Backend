import React, { useEffect, useState } from 'react'
import { CRow, CCol, CPagination, CPaginationItem } from '@coreui/react'
import axios from 'axios'

const PaginationComponent = ({ apiEndpoint, authToken, onDataFetched, filters = {} }) => {
  // Pagination state
  const [currentPage, setCurrentPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [total, setTotal] = useState(0)
  const [perPage, setPerPage] = useState(10)

  // Fetch data from API
  const fetchData = (page = 1) => {
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
        },
      })
      .then((response) => {
        const data = response.data.data
        onDataFetched(data) // Pass data back to parent component
        setCurrentPage(response.data.meta.current_page)
        setLastPage(response.data.meta.last_page)
        setTotal(response.data.meta.total)
        setPerPage(response.data.meta.per_page)
      })
      .catch((error) => {
        console.error('There was an error fetching the data!', error)
      })
  }

  useEffect(() => {
    fetchData()
  }, [filters])

  const handlePageChange = (page) => {
    fetchData(page)
  }

  return (
    <>
      {/* Pagination Controls */}
      <CRow className="mt-3">
        <CCol className="d-flex justify-content-center">
          <CPagination aria-label="Page navigation">
            <CPaginationItem
              disabled={currentPage === 1}
              onClick={() => handlePageChange(currentPage - 1)}
            >
              Previous
            </CPaginationItem>
            {/* Display page numbers */}
            {Array.from({ length: lastPage }, (_, index) => index + 1).map((page) => (
              <CPaginationItem
                key={page}
                active={page === currentPage}
                onClick={() => handlePageChange(page)}
              >
                {page}
              </CPaginationItem>
            ))}
            <CPaginationItem
              disabled={currentPage === lastPage}
              onClick={() => handlePageChange(currentPage + 1)}
            >
              Next
            </CPaginationItem>
          </CPagination>
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
