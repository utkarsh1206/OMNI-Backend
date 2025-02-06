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

const UserGroups = () => {
  const navigate = useNavigate()
  const [userGroups, setUserGroups] = useState([])
  const { authToken } = useContext(AuthContext)
  const [searchQuery, setSearchQuery] = useState('')
  const initialFilters = {
    name: '',
    campaign_id: '',
    status: '',
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

  const handleDeleteUserGroup = async (id, name) => {
    if (window.confirm('Are you sure to delete ' + name + '?')) {
      console.log('pressed OK')
      try {
        let response = await axios.delete(`${BASE_URL}/api/usergroup/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          // Refetch users after deletion
          setUserGroups((prev) => prev.filter((userGroup) => userGroup.id !== id)) // Immediately update the UI
        }
      } catch (error) {
        console.error('Operation failed:', error)
        alert('An error occurred while processing your request.')
      }
    } else {
      console.log('pressed cancel')
    }
  }

  // Define custom filter card for UserGroups
  const renderUserGroupFilters = (filters, setFilters) => (
    <>
      <CRow className="mb-3">
        <CCol xs={12} md={4}>
          <label htmlFor="name" className="form-label">
            UserGroup Name:
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
        <CCol xs={12} md={3}>
          <label htmlFor="campaign_id" className="form-label">
            Campaign_id:
          </label>
          <CFormInput
            id="campaign_id"
            type="text"
            name="campaign_id"
            value={filters.campaign_id || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, campaign_id: e.target.value }))}
            placeholder="Enter campaign_id"
          />
        </CCol>
        <CCol xs={12} md={3}>
          <label htmlFor="end-time" className="form-label">
            Status:
          </label>
          <CFormInput
            id="status"
            type="text"
            name="status"
            value={filters.status || ''}
            onChange={(e) => setFilters((prev) => ({ ...prev, status: e.target.value }))}
            placeholder="Enter status"
          />
        </CCol>
      </CRow>
    </>
  )

  return (
    <>
      {/* <h6>User Groups Listing</h6> */}

      <CRow>
        <CCol>
          <CContainer className="mb-3">
            <CRow fluid responsive>
              <CCol md={8}>
                <Link to="/user/crt_usr_grp">
                  <CButton color="primary" className=" me-2" size="sm">
                    Create User Group
                  </CButton>
                </Link>
                <CButton
                  color="primary"
                  className=" me-2"
                  size="sm"
                  onClick={handleDeleteUserGroup}
                >
                  Delete User Group
                </CButton>
                <CButton color="primary" className=" me-2" size="sm">
                  Download CSV
                </CButton>
              </CCol>
              <SearchAndFilter
                onSearch={setSearchQuery} // Callback for live search
                onApplyFilters={handleApplyFilters}
                onResetFilters={handleResetFilters} // Callback for reset filters
                filters={filters} // Current filters
                setFilters={setFilters} // Update filters
                renderFilters={renderUserGroupFilters} // Custom filter UI for users
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
                <CTableHeaderCell scope="col">Campaign ID</CTableHeaderCell>
                <CTableHeaderCell scope="col">Description</CTableHeaderCell>
                <CTableHeaderCell scope="col">Status</CTableHeaderCell>
                <CTableHeaderCell scope="col">Registration Date</CTableHeaderCell>
                <CTableHeaderCell scope="col">Controls</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {userGroups.map((group, index) => (
                <CTableRow key={group.id}>
                  <CTableDataCell>{index + 1 + (currentPage - 1) * perPage}</CTableDataCell>
                  <CTableDataCell>{group.name}</CTableDataCell>
                  <CTableDataCell>{group.campaign_id}</CTableDataCell>
                  <CTableDataCell>{group.description}</CTableDataCell>
                  <CTableDataCell>{group.status}</CTableDataCell>
                  <CTableDataCell>{formatDate(group.created_at)}</CTableDataCell>
                  <CTableDataCell>
                    <CRow className="justify-content-center mx-0">
                      <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="Edit">
                          <CButton
                            color="warning"
                            size="sm"
                            onClick={() => navigate(`/user/edit_usergroup/${group.id}`)}
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
                            onClick={() => handleDeleteUserGroup(group.id, group.name)}
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
            apiEndpoint={`${BASE_URL}/api/usergroup`}
            authToken={authToken}
            onDataFetched={setUserGroups}
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

export default UserGroups
