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

const Incident = () => {
  const navigate = useNavigate()
  const [incident, setIncident] = useState([])
  const { authToken } = useContext(AuthContext)
  const [searchQuery, setSearchQuery] = useState('')
  const [filters, setFilters] = useState({
    name: '',
    department_id: '',
    category_id: '',
    priority: '',
    sla_id: '',
  })

  const [currentPage, setCurrentPage] = useState(1) // Add state to track the current page
  const [perPage, setPerPage] = useState(10) // Add state to track the current page
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
    setPerPage(perPage) // Update the current page when pagination changes
  }

  const handleDeleteIncident = async (id, name) => {
    if (window.confirm('Are you sure to delete ' + name + '?')) {
      console.log('pressed OK')
      try {
        let response = await axios.delete(`${BASE_URL}/api/incident-types/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          // Refetch incidents after deletion
          setIncident((prev) => prev.filter((incident) => incident.id !== id)) // Immediately update the UI
        }
      } catch (error) {
        console.error('Operation failed:', error)
        alert('An error occurred while processing your request.')
      }
    } else {
      console.log('pressed cancel')
    }
  }

  // Define custom filter card for Categorys
  const renderIncidentFilters = (filters, setFilters) => (
    <CRow className="mb-3">
      <CCol xs={12} md={4}>
        <label htmlFor="name" className="form-label">
          Incident Name:
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
      <CCol xs={12} md={4}>
        <label htmlFor="department_id" className="form-label">
          Department:
        </label>
        <CFormInput
          id="department_id"
          type="text"
          name="department_id"
          value={filters.department_id || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, department_id: e.target.value }))}
          placeholder="Enter department_id"
        />
      </CCol>
      <CCol xs={12} md={4}>
        <label htmlFor="category_id" className="form-label">
          Category:
        </label>
        <CFormInput
          id="category_id"
          type="text"
          name="category_id"
          value={filters.category_id || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, category_id: e.target.value }))}
          placeholder="Enter category_id"
        />
      </CCol>
      <CCol xs={12} md={4}>
        <label htmlFor="priority" className="form-label">
          Priority:
        </label>
        <CFormInput
          id="priority"
          type="text"
          name="priority"
          value={filters.priority || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, priority: e.target.value }))}
          placeholder="Enter priority"
        />
      </CCol>
      <CCol xs={12} md={4}>
        <label htmlFor="sla_id" className="form-label">
          SLA:
        </label>
        <CFormInput
          id="sla_id"
          type="text"
          name="sla_id"
          value={filters.sla_id || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, sla_id: e.target.value }))}
          placeholder="Enter sla_id"
        />
      </CCol>
    </CRow>
  )

  return (
    <>
      {/* <h6>Category Listing</h6> */}

      <CRow>
        <CCol>
          <CContainer className="mb-3">
            <CRow fluid responsive>
              <CCol md={8}>
                <Link to="/incdnt_type/create_incident">
                  <CButton color="primary" className=" me-2" size="sm">
                    Create Incident Type
                  </CButton>
                </Link>
              </CCol>
              <SearchAndFilter
                onSearch={setSearchQuery} // Callback for live search
                onApplyFilters={handleApplyFilters}
                onResetFilters={handleResetFilters} // Callback for reset filters
                filters={filters} // Current filters
                setFilters={setFilters} // Update filters
                renderFilters={renderIncidentFilters} // Custom filter UI for users
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
                <CTableHeaderCell scope="col">Category</CTableHeaderCell>
                <CTableHeaderCell scope="col">Default Priority</CTableHeaderCell>
                <CTableHeaderCell scope="col">SLA Assigned</CTableHeaderCell>
                <CTableHeaderCell scope="col">Controls</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {incident.map((incident, index) => (
                <CTableRow key={incident.id}>
                  <CTableDataCell>{index + 1 + (currentPage - 1) * perPage}</CTableDataCell>
                  <CTableDataCell>{incident.name}</CTableDataCell>
                  <CTableDataCell>{incident.description}</CTableDataCell>
                  <CTableDataCell>{incident.category.name}</CTableDataCell>
                  <CTableDataCell>{incident.priority}</CTableDataCell>
                  <CTableDataCell>{incident.sla_id}</CTableDataCell>
                  <CTableDataCell>
                    <CRow className="justify-content-center mx-0">
                      <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="Edit">
                          <CButton
                            color="warning"
                            size="sm"
                            onClick={() => navigate(`/incdnt_type/edit_incident/${incident.id}`)}
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
                            onClick={() => handleDeleteIncident(incident.id, incident.name)}
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
            apiEndpoint={`${BASE_URL}/api/incident-types`}
            authToken={authToken}
            onDataFetched={setIncident}
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

export default Incident
