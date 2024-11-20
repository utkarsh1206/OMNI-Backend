import React, { useContext, useEffect, useState } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
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
import CIcon from '@coreui/icons-react'
import { cilPencil, cilXCircle } from '@coreui/icons'
import PaginationComponent from '../../components/PaginationComponent'

const Users = () => {
  const navigate = useNavigate()
  const [users, setUsers] = useState([])
  const { authToken } = useContext(AuthContext)

  const [liveSearch, setLiveSearch] = useState(null)

  const [searchQuery, setSearchQuery] = useState('')

  useEffect(() => {
    const timer = setTimeout(() => {
      setSearchQuery(liveSearch) // Debounce input
    }, 500)

    return () => clearTimeout(timer)
  }, [liveSearch])

  const [filters, setFilters] = useState({
    // email: '',
    // status: '',
    // Add more filters here as needed
  })

  const handleFilterChange = (e) => {
    const { name, value } = e.target
    setFilters((prevFilters) => ({
      ...prevFilters,
      [name]: value, // Dynamically update the property based on input name
    }))
  }

  const handleDeleteUser = async (id, username) => {
    if (window.confirm('Are you sure to delete ' + username + '?')) {
      console.log('pressed OK')
      try {
        let response = await axios.delete(`${BASE_URL}/api/users/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
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
    } else {
      console.log('pressed cancel')
    }
  }

  // Format date
  const formatDate = (string) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' }
    return new Date(string).toLocaleDateString([], options)
  }

  return (
    <>
      <CRow>
        <CCol xs={12}>
          <CCard className="mb-4">
            <CCardHeader className="d-flex justify-content-between align-items-center">
              {/* Left Section */}
              <div>
                Search for <strong>Users</strong> <small>Filters will be filled later</small>
              </div>

              {/* Right Section: Search Text and Input */}
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
            </CCardHeader>
            <CCardBody>
              This is a test body
              <div>
                <label>
                  Email:
                  <input
                    type="text"
                    name="email"
                    value={filters.email}
                    onChange={handleFilterChange}
                    placeholder="Enter email"
                  />
                </label>
              </div>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>
      <hr />
      <CRow>
        <CCol>
          <CContainer>
            <CRow className="mb-4 align-items-center">
              {/* Left Section: Create, Delete, Download */}
              <CCol md={6} className="d-flex">
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
              <CCol md={6}>
                <div className="d-flex justify-content-end align-items-center">
                  {/* Bulk User Creation */}
                  <CButton color="primary" className="me-2" size="sm">
                    Bulk User Creation
                  </CButton>

                  {/* Search Label */}

                  {/* PerPage Display */}
                  {/* <span className="me-2">PerPage Display:</span>
                  <CFormSelect
                    id="perPage"
                    name="perPage"
                    value="perpage"
                    className="me-2"
                    size="sm"
                  >
                    <option key="1" value="1">
                      1
                    </option>
                    <option key="10" value="10">
                      10
                    </option>
                    <option key="100" value="100">
                      100
                    </option>
                  </CFormSelect> */}
                </div>
              </CCol>
            </CRow>
          </CContainer>

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
              {users.map((user) => (
                <CTableRow key={user.id}>
                  <CTableDataCell>{user.id}</CTableDataCell>
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
          />
        </CCol>
      </CRow>
    </>
  )
}

export default Users
