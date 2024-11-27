import React, { useContext, useState } from 'react'
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
  const [filters, setFilters] = useState({
    email: '',
    status: '',
  })

  const [currentPage, setCurrentPage] = useState(1) // Add state to track the current page
  const [perPage, setPerPage] = useState(10) // Add state to track the current page

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
        console.error('Operation failed:', error)
        alert('An error occurred while processing your request.')
      }
    }
  }

  // Define custom filter card for Users
  const renderUserFilters = (filters, setFilters) => (
    <>
      <div>
        <label>
          Email:
          <input
            type="text"
            name="email"
            value={filters.email || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, email: e.target.value }))}
            placeholder="Enter email"
          />
        </label>
      </div>
      <div>
        <label>
          Status:
          <select
            name="status"
            value={filters.status || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
          >
            <option value="">Select status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </label>
      </div>
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
                <CButton color="primary" className="me-2" size="sm" onClick={handleDeleteUser}>
                  Delete User
                </CButton>
                <CButton color="primary" className="me-2" size="sm">
                  Download CSV
                </CButton>
              </CCol>
              {/* Right Section: Bulk User Creation, Search, PerPage */}
              <CCol xs="auto" className="ms-auto pe-0">
                {/* <div className="d-flex justify-content-end align-items-center"> */}
                {/* Bulk User Creation */}
                <CButton color="primary" size="sm">
                  Bulk User Creation
                </CButton>
                {/* </div> */}
              </CCol>
              <SearchAndFilter
                onSearch={setSearchQuery} // Callback for live search
                filters={filters} // Current filters
                setFilters={setFilters} // Update filters
                renderFilters={renderUserFilters} // Custom filter UI for users
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
              {users.map((user, index) => (
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
              ))}
            </CTableBody>
          </CTable>
          {/* Reusable PaginationComponent */}
          <PaginationComponent
            apiEndpoint={`${BASE_URL}/api/users`}
            authToken={authToken}
            onDataFetched={setUsers}
            filters={filters}
            search={searchQuery}
            onPageChange={handlePageChange}
            perPageValue={handlePerPageValue}
          />
        </CCol>
      </CRow>
    </>
  )
}

export default Users
