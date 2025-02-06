import React, { useContext, useEffect, useState } from 'react'
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
import { formatDate } from '../../utils/DateUtils'
import SearchAndFilter from '../../components/SearchAndFilter'

const Departments = () => {
  const navigate = useNavigate()
  const [departments, setDepartments] = useState([])
  const { authToken } = useContext(AuthContext)
  const [searchQuery, setSearchQuery] = useState('')
  const initialFilters = {
    name: '',
  }
  const [filters, setFilters] = useState(initialFilters)

  const [currentPage, setCurrentPage] = useState(1) // Add state to track the current page
  const [perPage, setPerPage] = useState(10) // Add state to track the current page
  const [appliedFilters, setAppliedFilters] = useState({})

  const handleApplyFilters = (newFilters) => {
    setAppliedFilters(newFilters) // Update applied filters only on button click
  }
  const handleResetFilters = () => {
    setFilters(initialFilters) // Clear filter state
    setAppliedFilters({}) // Clear applied filters
    setSearchQuery('') // Clear live search
  }

  const handlePageChange = (page) => {
    setCurrentPage(page) // Update the current page when pagination changes
  }
  const handlePerPageValue = (perPage) => {
    setPerPage(perPage) // Update the current page when pagination changes
  }

  const handleDeleteDepartment = async (id, name) => {
    if (window.confirm('Are you sure to delete ' + name + '?')) {
      console.log('pressed OK')
      try {
        let response = await axios.delete(`${BASE_URL}/api/departments/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          // Refetch users after deletion
          setDepartments((prev) => prev.filter((department) => department.id !== id)) // Immediately update the UI
        }
      } catch (error) {
        console.error('Operation failed:', error)
        alert('An error occurred while processing your request.')
      }
    } else {
      console.log('pressed cancel')
    }
  }

  // Define custom filter card for Departments
  const renderDepartmentFilters = (filters, setFilters) => (
    <CRow className="mb-3">
      <CCol xs={12} md={4}>
        <label htmlFor="name" className="form-label">
          Department Name:
        </label>
        <CFormInput
          id="name"
          type="text"
          name="name"
          value={filters.name || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, name: e.target.value }))}
          placeholder="Enter name"
        />
      </CCol>
    </CRow>
  )

  return (
    <>
      {/* <h6>Department Listing</h6> */}

      <CRow>
        <CCol>
          <CContainer className="mb-3">
            <CRow fluid responsive>
              <CCol md={8}>
                <Link to="/department/create_departments">
                  <CButton color="primary" className=" me-2" size="sm">
                    Create Department
                  </CButton>
                </Link>
              </CCol>
              <SearchAndFilter
                onSearch={setSearchQuery} // Callback for live search
                onApplyFilters={handleApplyFilters}
                onResetFilters={handleResetFilters} // Callback for reset filters
                filters={filters} // Current filters
                setFilters={setFilters} // Update filters
                renderFilters={renderDepartmentFilters} // Custom filter UI for users
                initialFilters={initialFilters}
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
                <CTableHeaderCell scope="col">Controls</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {departments.map((department, index) => (
                <CTableRow key={department.id}>
                  <CTableDataCell>{index + 1 + (currentPage - 1) * perPage}</CTableDataCell>
                  <CTableDataCell>{department.name}</CTableDataCell>
                  <CTableDataCell>{department.description}</CTableDataCell>
                  <CTableDataCell>
                    <CRow className="justify-content-center mx-0">
                      <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="Edit">
                          <CButton
                            color="warning"
                            size="sm"
                            onClick={() =>
                              navigate(`/department/edit_departments/${department.id}`)
                            }
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
                            onClick={() => handleDeleteDepartment(department.id, department.name)}
                          >
                            <CIcon icon={cilXCircle} />
                          </CButton>
                        </CTooltip>
                      </CCol>
                    </CRow>
                  </CTableDataCell>
                </CTableRow>
              ))}
            </CTableBody>
          </CTable>
          {/* Reusable PaginationComponent */}
          <PaginationComponent
            apiEndpoint={`${BASE_URL}/api/departments`}
            authToken={authToken}
            onDataFetched={setDepartments}
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

export default Departments
