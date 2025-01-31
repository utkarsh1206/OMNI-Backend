import React, { useContext, useState } from 'react'
import {
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
} from '@coreui/react'
import { CTooltip } from '@coreui/react'

import axios from 'axios'
import { useNavigate } from 'react-router-dom'
import { AuthContext } from '../../context/AuthContext'
import PaginationComponent from '../../components/PaginationComponent'
import { BASE_URL } from '../../config'
import { formatDateWithTime } from '../../utils/DateUtils'
import SearchAndFilter from '../../components/SearchAndFilter'

const AuditReport = () => {
  const navigate = useNavigate()
  const [incident, setIncident] = useState([])
  const { authToken } = useContext(AuthContext)
  const [searchQuery, setSearchQuery] = useState('')
  const [filters, setFilters] = useState({
    username: '',
    ipaddress: '',
    module_name: '',
    action: '',
    request_params: '',
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

  // Define custom filter card for Categorys
  const renderAuditFilters = (filters, setFilters) => (
    <CRow className="mb-3">
      <CCol xs={12} md={4}>
        <label htmlFor="username" className="form-label">
          Username:
        </label>
        <CFormInput
          id="username"
          type="text"
          name="username"
          value={filters.username || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, username: e.target.value }))}
          placeholder="Enter username"
        />
      </CCol>
      <CCol xs={12} md={4}>
        <label htmlFor="ipaddress" className="form-label">
          Remote IP:
        </label>
        <CFormInput
          id="ipaddress"
          type="text"
          name="ipaddress"
          value={filters.ipaddress || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, ipaddress: e.target.value }))}
          placeholder="Enter Remote IP"
        />
      </CCol>
      <CCol xs={12} md={4}>
        <label htmlFor="module_name" className="form-label">
          Module:
        </label>
        <CFormInput
          id="module_name"
          type="text"
          name="module_name"
          value={filters.module_name || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, module_name: e.target.value }))}
          placeholder="Enter Module Name"
        />
      </CCol>
      <CCol xs={12} md={4}>
        <label htmlFor="action" className="form-label">
          Action:
        </label>
        <CFormInput
          id="action"
          type="text"
          name="action"
          value={filters.action || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, action: e.target.value }))}
          placeholder="Enter Action"
        />
      </CCol>
      <CCol xs={12} md={4}>
        <label htmlFor="request_params" className="form-label">
          Request Parameters:
        </label>
        <CFormInput
          id="request_params"
          type="text"
          name="request_params"
          value={filters.request_params || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, request_params: e.target.value }))}
          placeholder="Enter request_params"
        />
      </CCol>
    </CRow>
  )

  return (
    <>
      <CRow>
        <CCol>
          <CContainer className="mb-3">
            <CRow fluid responsive>
              <CCol md={8}>
                <h5>Audit Report</h5>
              </CCol>
              <SearchAndFilter
                onSearch={setSearchQuery} // Callback for live search
                onApplyFilters={handleApplyFilters}
                onResetFilters={handleResetFilters} // Callback for reset filters
                filters={filters} // Current filters
                setFilters={setFilters} // Update filters
                renderFilters={renderAuditFilters} // Custom filter UI for users
              />
            </CRow>
            <hr />
          </CContainer>
          <CTable hover bordered responsive="md">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell>
                <CTableHeaderCell scope="col">UserName</CTableHeaderCell>
                <CTableHeaderCell scope="col">Remote IP</CTableHeaderCell>
                <CTableHeaderCell scope="col">Access Time</CTableHeaderCell>
                <CTableHeaderCell scope="col">Module</CTableHeaderCell>
                <CTableHeaderCell scope="col">Action</CTableHeaderCell>
                <CTableHeaderCell scope="col">Request Parameters</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {incident.map((incident, index) => (
                <CTableRow key={incident.id}>
                  <CTableDataCell>{index + 1 + (currentPage - 1) * perPage}</CTableDataCell>
                  <CTableDataCell>{incident.user_name}</CTableDataCell>
                  <CTableDataCell>{incident.ipaddress}</CTableDataCell>
                  <CTableDataCell>{formatDateWithTime(incident.created_at)}</CTableDataCell>
                  <CTableDataCell>{incident.action.replace(/^[A-Z]+\sapi\//, '')}</CTableDataCell>
                  <CTableDataCell>{incident.request_method}</CTableDataCell>
                  {/* <CTableDataCell>{incident.request_params}</CTableDataCell> */}
                  <CTableDataCell
                    style={{
                      whiteSpace: 'nowrap',
                      overflow: 'hidden',
                      textOverflow: 'ellipsis',
                      maxWidth: '100px', // Set a max width to control when the ellipsis appears
                    }}
                  >
                    <CTooltip content={incident.request_params}>
                      <span>{incident.request_params}</span>
                    </CTooltip>
                  </CTableDataCell>
                </CTableRow>
              ))}
            </CTableBody>
          </CTable>
          {/* Reusable PaginationComponent */}
          <PaginationComponent
            apiEndpoint={`${BASE_URL}/api/audit-logs`}
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

export default AuditReport
