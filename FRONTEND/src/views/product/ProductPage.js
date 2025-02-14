import React, { useContext, useEffect, useState, useRef, useCallback } from "react"
import {
  CButton,
  CCol,
  CContainer,
  CFormInput,
  CRow,
  CTable,
  CTableBody,
  CTableDataCell,
  CTableHead,
  CFormSelect,
  CTableHeaderCell,
  CTableRow,
  CTooltip,
} from '@coreui/react'
import axios from 'axios'
import { Link, useNavigate } from 'react-router-dom'
import { AuthContext } from '../../context/AuthContext'
import PaginationComponent from '../../components/PaginationComponent'
import CIcon from '@coreui/icons-react'
import { cilPencil, cilXCircle } from '@coreui/icons'
import { BASE_URL } from '../../config'

import SearchAndFilter from '../../components/SearchAndFilter'

const Product = () => {
  const navigate = useNavigate()
  const { authToken } = useContext(AuthContext)

  // State variables
  const [products, setProducts] = useState([])
  const [searchQuery, setSearchQuery] = useState('')
  const [filters, setFilters] = useState({
    name: '',
    status: '',
  })
  const [appliedFilters, setAppliedFilters] = useState({})
  const [currentPage, setCurrentPage] = useState(1)
  const [perPage, setPerPage] = useState(10)

  // Handle applying filters
  const handleApplyFilters = (newFilters) => {
    setAppliedFilters(newFilters);
    setCurrentPage(1);
  }

  // Handle resetting filters
  const handleResetFilters = () => {
    setFilters({ name: '', status: '' })
    setAppliedFilters({})
    setSearchQuery('')
    setCurrentPage(1);
  }

  // Handle pagination changes
  const handlePageChange = (page) => setCurrentPage(page)
  const handlePerPageValue = (perPage) => setPerPage(perPage)

  // Handle product deletion
  const handleDeleteProduct = async (id, name) => {
    if (window.confirm(`Are you sure to delete ${name}?`)) {
      try {
        const response = await axios.delete(`${BASE_URL}/api/products/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        })
        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          setProducts((prev) => prev.filter((product) => product.id !== id))
        }
      } catch (error) {
        console.error('Error deleting product:', error)
        alert(error.response.data.message)
      }
    }
  }

  // Fetching products with filters
  useEffect(() => {
    if (searchQuery !== "") {
      setCurrentPage(1)
    }
    fetchProducts()
  }, [searchQuery, appliedFilters, currentPage, perPage])

  const fetchProducts = useCallback(async () => {
    try {
      const response = await axios.get(`${BASE_URL}/api/products`, {
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${authToken}`,
        },
        params: {
          search: searchQuery,
          ...appliedFilters,
          page: currentPage,
          per_page: perPage,
        },
      })
      setProducts(response.data.data)
    } catch (error) {
      console.error("Error fetching products:", error)
    }
  }, [searchQuery, appliedFilters, currentPage, perPage, authToken])

  const renderProductFilters = (filters, setFilters) => (
    <CRow className="mb-3">
      <CCol xs={12} md={6}>
        <label htmlFor="name" className="form-label">Product Name:</label>
        <CFormInput
          id="name"
          type="text"
          name="name"
          value={filters.name || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, name: e.target.value }))}
          placeholder="Enter product name"
        />
      </CCol>
      <CCol xs={12} md={6}>
        <label htmlFor="status" className="form-label">
          Status:
        </label>
        <CFormSelect
          id="status"
          name="status"
          value={filters.status || ""}
          onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
        >
          <option value="">All</option>
          <option value="true">Active</option>
          <option value="false">Inactive</option>
        </CFormSelect>
      </CCol>
    </CRow>
  )

  // Truncated Description Component with useLayoutEffect for optimization
  const TruncatedDescription = ({ text, maxWidth }) => {
    const [truncatedText, setTruncatedText] = useState(text)
    const textRef = useRef(null)

    useEffect(() => {
      const checkOverflow = () => {
        if (textRef.current) {
          const element = textRef.current
          if (element.offsetWidth > maxWidth) {
            let truncated = text
            while (element.offsetWidth > maxWidth && truncated.length > 0) {
              truncated = truncated.slice(0, -1)
              element.textContent = truncated + "..."
            }
            setTruncatedText(truncated + "...")
          } else {
            setTruncatedText(text)
          }
        }
      }

      checkOverflow()
      window.addEventListener("resize", checkOverflow)
      return () => window.removeEventListener("resize", checkOverflow)
    }, [text, maxWidth])

    return (
      <span ref={textRef} title={text}>
        {truncatedText}
      </span>
    )
  }

  return (
    <>
      <CRow>
        <CCol>
          <CContainer className="mb-3">
            <CRow fluid responsive>
              <CCol md={8}>
                <Link to="/product/create">
                  <CButton color="primary" className="me-2" size="sm">Create Product</CButton>
                </Link>
              </CCol>
              <SearchAndFilter
                onSearch={setSearchQuery}
                onApplyFilters={handleApplyFilters}
                onResetFilters={handleResetFilters}
                filters={filters}
                setFilters={setFilters}
                renderFilters={renderProductFilters}
              />
            </CRow>
            <hr />
          </CContainer>

          <CTable hover bordered responsive="md">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell>
                <CTableHeaderCell scope="col">Name</CTableHeaderCell>
                <CTableHeaderCell scope="col">Description</CTableHeaderCell>
                <CTableHeaderCell scope="col">Status</CTableHeaderCell>
                <CTableHeaderCell scope="col">Controls</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {products.length > 0 ? (
                products.map((product, index) => (
                  <CTableRow key={product.id}>
                    <CTableDataCell width='60px'>{searchQuery !== "" ? index + 1 : index + 1 + (currentPage - 1) * perPage}</CTableDataCell>
                    <CTableDataCell>{product.name}</CTableDataCell>
                    <CTableDataCell>
                      <TruncatedDescription text={product.description} maxWidth={500} />
                    </CTableDataCell>
                    <CTableDataCell width='100px'>{product.status ? 'Active' : 'Inactive'}</CTableDataCell>
                    <CTableDataCell width='120px'>
                      <CRow className="justify-content-center mx-0">
                        <CCol className="mx-auto d-flex justify-content-center">
                          <CTooltip content="Edit">
                            <CButton
                              color="warning"
                              size="sm"
                              onClick={() => navigate(`/product/edit/${product.id}`)}
                            >
                              <CIcon icon={cilPencil} />
                            </CButton>
                          </CTooltip>
                        </CCol>
                        <CCol className="mx-auto d-flex justify-content-center">
                          <CTooltip content="Delete">
                            <CButton
                              color="danger"
                              size="sm"
                              onClick={() => handleDeleteProduct(product.id, product.name)}
                            >
                              <CIcon icon={cilXCircle} />
                            </CButton>
                          </CTooltip>
                        </CCol>
                      </CRow>
                    </CTableDataCell>
                  </CTableRow>
                ))
              ) : (
                <CTableRow>
                  <CTableDataCell colSpan="5" className="text-center">
                    No data found
                  </CTableDataCell>
                </CTableRow>
              )}
            </CTableBody>
          </CTable>

          {/* Pagination Component */}
          <PaginationComponent
            apiEndpoint={`${BASE_URL}/api/products`}
            authToken={authToken}
            onDataFetched={setProducts}
            filters={appliedFilters}
            search={searchQuery}
            onPageChange={handlePageChange}
            perPageValue={handlePerPageValue}
          />
        </CCol>
      </CRow>
    </>
  )
}

export default Product
