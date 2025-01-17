import React, { useContext, useEffect, useState } from 'react'
import {
  CButton,
  CCol,
  CContainer,
  CRow,
  CTable,
  CTableBody,
  CTableDataCell,
  CTableHead,
  CTableHeaderCell,
  CTableRow,
  CTooltip,
  CFormLabel,
  CFormSelect,
  CFormInput,
} from '@coreui/react'

import { Link, useNavigate } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { cilPencil, cilXCircle } from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../context/AuthContext'
import PaginationComponent from '../../components/PaginationComponent'
import axios from 'axios'
import SearchAndFilter from '../../components/SearchAndFilter'
import { formatDate } from '../../utils/DateUtils'

const Phones = () => {
  const navigate = useNavigate()
  const { authToken } = useContext(AuthContext)
  const [phones, setPhones] = useState([])

  const [filters, setFilters] = useState({
    extension: '',
    login: '',
  })
  const [searchQuery, setSearchQuery] = useState('')
  const [currentPage, setCurrentPage] = useState(1)
  const [perPage, setPerPage] = useState(10)

  const [appliedFilters, setAppliedFilters] = useState({})

  const handleApplyFilters = (newFilters) => {
    setAppliedFilters(newFilters) // Update applied filters only on button click
  }
  const handleResetFilters = () => {
    setFilters({}) // Clear filter state
    setAppliedFilters({}) // Clear applied filters
    setSearchQuery('') // Clear live search
  }

  const handlePageChange = (page) => {
    setCurrentPage(page) // Update the current page when pagination changes
  }
  const handlePerPageValue = (perPage) => {
    setPerPage(perPage)
  }

  const handleDeletePhones = async (extension) => {
    if (window.confirm('Are you sure to delete?')) {
      try {
        let response = await axios.delete(`${BASE_URL}/api/phones/${extension}`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          setPhones((prev) => prev.filter((phones) => phones.extension !== extension))
        }
      } catch (error) {
        console.error('Operation failed:', error)
        alert('An error occurred while processing your request.')
      }
    }
  }

  const renderPhoneFilters = (filters, setFilters) => (
    <CRow style={{ display: 'block ruby', alignItems: 'center' }}>
      <CCol md="6" className="mb-3">
        <CFormLabel htmlFor="extension">Extension</CFormLabel>
        <CFormInput
          id="extension"
          type="text"
          name="extension"
          value={filters.extension || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, extension: e.target.value }))}
          placeholder="Enter Extension"
        />
      </CCol>

      <CCol md="6" className="mb-3">
        <CFormLabel htmlFor="phone">Phone</CFormLabel>
        <CFormInput
          id="phone"
          type="text"
          name="phone"
          value={filters.phone || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, phone: e.target.value }))}
          placeholder="Enter Phone"
        />
      </CCol>
    </CRow>
  )

  return (
    <CRow>
      <CCol>
        <CContainer>
          <CRow className="mb-3 align-items-center">
            <CCol className="d-flex">
              <Link to="/phones/create_phone">
                <CButton color="primary" className="me-2" size="sm">
                  Create Phone
                </CButton>
              </Link>
              {/* <CButton color="primary" className="me-2" size="sm" >
                Delete Phone
              </CButton> */}
            </CCol>
            <SearchAndFilter
              onSearch={setSearchQuery}
              onApplyFilters={handleApplyFilters}
              onResetFilters={handleResetFilters} // Callback for reset filters
              filters={filters}
              setFilters={setFilters}
              renderFilters={renderPhoneFilters} // Custom filter UI for users
            />
          </CRow>
        </CContainer>
        <hr />
        <CTable hover bordered responsive="md">
          <CTableHead color="light">
            <CTableRow>
              <CTableHeaderCell scope="col">#</CTableHeaderCell>
              <CTableHeaderCell scope="col">Extension</CTableHeaderCell>
              <CTableHeaderCell scope="col">Phone</CTableHeaderCell>
              <CTableHeaderCell scope="col">Created Date</CTableHeaderCell>
              <CTableHeaderCell scope="col">Updated Date</CTableHeaderCell>
              <CTableHeaderCell scope="col">Action</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            {phones.map((phone, index) => (
              <CTableRow key={phone.extension}>
                <CTableDataCell>{index + 1}</CTableDataCell>
                <CTableDataCell>{phone.extension}</CTableDataCell>
                <CTableDataCell>{phone.login}</CTableDataCell>
                <CTableDataCell>{formatDate(phone.created_at)}</CTableDataCell>
                <CTableDataCell>{formatDate(phone.updated_at)}</CTableDataCell>
                <CTableDataCell>
                  <div style={{ display: 'flex', justifyContent: 'center', gap: '8px' }}>
                    <CTooltip content="Edit">
                      <CButton
                        color="warning"
                        size="sm"
                        onClick={() => navigate(`/phones/edit/${phone.extension}`)}
                      >
                        <CIcon icon={cilPencil} />
                      </CButton>
                    </CTooltip>
                    <CTooltip content="Delete">
                      <CButton
                        color="danger"
                        size="sm"
                        onClick={() => handleDeletePhones(phone.extension)}
                      >
                        <CIcon icon={cilXCircle} />
                      </CButton>
                    </CTooltip>
                  </div>
                </CTableDataCell>
              </CTableRow>
            ))}
          </CTableBody>
        </CTable>

        <PaginationComponent
          apiEndpoint={`${BASE_URL}/api/phones`}
          authToken={authToken}
          onDataFetched={setPhones}
          filters={appliedFilters}
          search={searchQuery}
          onPageChange={handlePageChange}
          perPageValue={handlePerPageValue}
        />
      </CCol>
    </CRow>
  )
}

export default Phones
