// import React, { useEffect, useState } from 'react'
// import { CRow, CCol, CPagination, CPaginationItem } from '@coreui/react'
// import axios from 'axios'

// const PaginationComponent = ({ apiEndpoint, authToken, onDataFetched, filters = {} }) => {
//   // Pagination state
//   const [currentPage, setCurrentPage] = useState(1)
//   const [lastPage, setLastPage] = useState(1)
//   const [total, setTotal] = useState(0)
//   const [perPage, setPerPage] = useState(10)

//   // Fetch data from API
//   const fetchData = (page = 1) => {
//     axios
//       .get(apiEndpoint, {
//         headers: {
//           'Content-Type': 'application/json',
//           Authorization: `Bearer ${authToken}`,
//         },
//         params: {
//           page: page,
//           per_page: perPage,
//           ...filters,
//         },
//       })
//       .then((response) => {
//         const data = response.data.data
//         onDataFetched(data) // Pass data back to parent component
//         setCurrentPage(response.data.meta.current_page)
//         setLastPage(response.data.meta.last_page)
//         setTotal(response.data.meta.total)
//         setPerPage(response.data.meta.per_page)
//       })
//       .catch((error) => {
//         console.error('There was an error fetching the data!', error)
//       })
//   }

//   useEffect(() => {
//     fetchData()
//   }, [filters])

//   const handlePageChange = (page) => {
//     fetchData(page)
//   }

//   return (
//     <>
//       {/* Pagination Controls */}
//       <CRow className="mt-3">
//         <CCol className="d-flex justify-content-center">
//           <CPagination aria-label="Page navigation">
//             <CPaginationItem
//               disabled={currentPage === 1}
//               onClick={() => handlePageChange(currentPage - 1)}
//             >
//               Previous
//             </CPaginationItem>
//             {/* Display page numbers */}
//             {Array.from({ length: lastPage }, (_, index) => index + 1).map((page) => (
//               <CPaginationItem
//                 key={page}
//                 active={page === currentPage}
//                 onClick={() => handlePageChange(page)}
//               >
//                 {page}
//               </CPaginationItem>
//             ))}
//             <CPaginationItem
//               disabled={currentPage === lastPage}
//               onClick={() => handlePageChange(currentPage + 1)}
//             >
//               Next
//             </CPaginationItem>
//           </CPagination>
//         </CCol>
//       </CRow>

//       {/* Display total items and current range */}
//       <CRow className="mb-2">
//         <CCol className="text-center">
//           Showing {(currentPage - 1) * perPage + 1} to {Math.min(currentPage * perPage, total)} of{' '}
//           {total}
//         </CCol>
//       </CRow>
//     </>
//   )
// }

// export default PaginationComponent


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
          </CPaginationItem>
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
          </CPaginationItem>
        )
        if (startPage > 2) {
          pages.push(<CPaginationItem key="start-ellipsis" disabled>...</CPaginationItem>)
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
          </CPaginationItem>
        )
      }

      if (endPage < lastPage) {
        if (endPage < lastPage - 1) {
          pages.push(<CPaginationItem key="end-ellipsis" disabled>...</CPaginationItem>)
        }
        pages.push(
          <CPaginationItem key={lastPage} onClick={() => handlePageChange(lastPage)}>
            {lastPage}
          </CPaginationItem>
        )
      }
    }

    return pages
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
