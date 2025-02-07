import React, { useContext, useState } from 'react'
import {
  CButton,
  CCol,
  CContainer,
  CFormInput,
  CFormSelect,
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
import { BASE_URL } from '../../config'
import PaginationComponent from '../../components/PaginationComponent'
import { formatDate } from '../../utils/DateUtils'
import SearchAndFilter from '../../components/SearchAndFilter'
import CIcon from '@coreui/icons-react'
import { cilPencil, cilXCircle } from '@coreui/icons'

const Users = () => {
  const navigate = useNavigate()
  const { authToken } = useContext(AuthContext)
  const [users, setUsers] = useState([])
  const [searchQuery, setSearchQuery] = useState('')
  const initialFilters = {
    name: '',
    email: '',
    status: '',
    mobile: '',
    usergroup_id: '',
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

  const handleDeleteUser = async (id, username) => {
    if (window.confirm('Are you sure to delete ' + username + '?')) {
      try {
        let response = await axios.delete(`${BASE_URL}/api/users/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          // Refetch users after deletion
          setUsers((prev) => prev.filter((user) => user.id !== id)) // Immediately update the UI
        }
      } catch (error) {
        console.error('Operation failed:', error.response.data.message)
        alert(error.response.data.message)
      }
    }
  }

  // Define custom filter card for Users
  const renderUserFilters = (filters, setFilters) => (
    <>
      <CRow>
        <CCol xs={12} md={3} className="mb-2">
          <label htmlFor="name" className="form-label">
            User Name:
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
        <CCol xs={12} md={3} className="mb-2">
          <label htmlFor="email" className="form-label">
            Email:
          </label>
          <CFormInput
            id="email"
            type="text"
            name="email"
            value={filters.email || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, email: e.target.value }))}
            placeholder="Enter email"
          />
        </CCol>
        <CCol xs={12} md={3} className="mb-2">
          <label htmlFor="status" className="form-label">
            Status:
          </label>
          {/* <CFormInput
            id="status"
            type="text"
            name="status"
            value={filters.status || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
            placeholder="Enter status"
          /> */}
          <CFormSelect
            id="status"
            name="status"
            value={filters.status || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
          >
            <option key="Active" value="Active">
              Active
            </option>
            <option key="Inactive" value="Inactive">
              Inactive
            </option>
          </CFormSelect>
        </CCol>
        <CCol xs={12} md={3} className="mb-2">
          <label htmlFor="mobile" className="form-label">
            Mobile:
          </label>
          <CFormInput
            id="mobile"
            type="text"
            name="mobile"
            value={filters.mobile || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, mobile: e.target.value }))}
            placeholder="Enter mobile"
          />
        </CCol>
        <CCol xs={12} md={3} className="mb-2">
          <label htmlFor="usergroup_id" className="form-label">
            UserGroup:
          </label>
          <CFormInput
            id="usergroup_id"
            type="text"
            name="usergroup_id"
            value={filters.usergroup_id || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, usergroup_id: e.target.value }))}
            placeholder="Enter usergroup_id"
          />
        </CCol>
      </CRow>
    </>
  )

  return (
    <>
      <CRow>
        <CCol>
          <CContainer>
            <CRow className="mb-3 align-items-center">
              <CCol className="d-flex">
                <Link to="/user/crt_usr">
                  <CButton color="primary" className="me-2" size="sm">
                    Create User
                  </CButton>
                </Link>
                {/* <CButton color="primary" className="me-2" size="sm" onClick={handleDeleteUser}>
                  Delete User
                </CButton>
                <CButton color="primary" className="me-2" size="sm">
                  Download CSV
                </CButton> */}
              </CCol>
              {/* Right Section: Bulk User Creation, Search, PerPage */}
              {/* <CCol xs="auto" className="ms-auto pe-0">
                <CButton color="primary" size="sm">
                  Bulk User Creation
                </CButton>
              </CCol> */}
              <SearchAndFilter
                onSearch={setSearchQuery} // Callback for live search
                onApplyFilters={handleApplyFilters}
                onResetFilters={handleResetFilters} // Callback for reset filters
                filters={filters} // Current filters
                setFilters={setFilters} // Update filters
                renderFilters={renderUserFilters} // Custom filter UI for users
                initialFilters={initialFilters}
              />
            </CRow>
          </CContainer>
          <hr />
          <CTable hover bordered responsive="md">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell>
                <CTableHeaderCell scope="col">Name</CTableHeaderCell>
                <CTableHeaderCell scope="col">Email</CTableHeaderCell>
                <CTableHeaderCell scope="col">Mobile</CTableHeaderCell>
                <CTableHeaderCell scope="col">Status</CTableHeaderCell>
                <CTableHeaderCell scope="col">UserGroup</CTableHeaderCell>
                <CTableHeaderCell scope="col">Registration Date</CTableHeaderCell>
                <CTableHeaderCell scope="col">Controls</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {users.length > 0 ? (
                users.map((user, index) => (
                <CTableRow key={user.id}>
                  {/* <CTableDataCell>{user.id}</CTableDataCell> */}
                  {/* <CTableDataCell>{index+1}</CTableDataCell> */}
                  <CTableDataCell>{index + 1 + (currentPage - 1) * perPage}</CTableDataCell>
                  <CTableDataCell>
                    {user.name_prefix} {user.first_name} {user.last_name}
                  </CTableDataCell>
                  <CTableDataCell>{user.email}</CTableDataCell>
                  <CTableDataCell>{user.mobile}</CTableDataCell>
                  <CTableDataCell>{user.status}</CTableDataCell>
                  <CTableDataCell>{user.usergroup_id}</CTableDataCell>
                  <CTableDataCell>{formatDate(user.created_at)}</CTableDataCell>
                  <CTableDataCell>
                    <CRow className="justify-content-center mx-0">
                      <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="Edit">
                          <CButton
                            color="warning"
                            size="sm"
                            onClick={() => navigate(`/user/edit/${user.id}`)}
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
                            onClick={() => handleDeleteUser(user.id, user.username)}
                          >
                            <CIcon icon={cilXCircle} />
                          </CButton>
                        </CTooltip>
                      </CCol>
                    </CRow>
                  </CTableDataCell>
                </CTableRow>
              ))
            ):(
              <CTableRow>
                  <CTableDataCell colSpan="8" className="text-center">
                    No data found
                  </CTableDataCell>
                </CTableRow>
            )}
            </CTableBody>
          </CTable>
          {/* Reusable PaginationComponent */}
          <PaginationComponent
            apiEndpoint={`${BASE_URL}/api/users`}
            authToken={authToken}
            onDataFetched={setUsers}
            // filters={filters}
            // search={searchQuery}
            filters={appliedFilters} // Use applied filters
            search={searchQuery} // Use live search
            onPageChange={handlePageChange}
            perPageValue={handlePerPageValue}
          />
        </CCol>
      </CRow>
    </>
  )
}

export default Users
