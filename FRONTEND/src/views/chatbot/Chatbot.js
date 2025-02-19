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

const Chatbot = () => {
  const navigate = useNavigate()
  const { authToken } = useContext(AuthContext)

  // State variables
  const [chatbots, setChatbots] = useState([])
  const [searchQuery, setSearchQuery] = useState('')
  const [filters, setFilters] = useState({
    question: '',
    status: '',
  })
  const [appliedFilters, setAppliedFilters] = useState({})
  const [currentPage, setCurrentPage] = useState(1)
  const [perPage, setPerPage] = useState(10)

  // Handle applying filters
  const handleApplyFilters = (newFilters) => {
    setAppliedFilters(newFilters)
    setCurrentPage(1) // Reset to first page when filters are applied
  }

  // Handle resetting filters
  const handleResetFilters = () => {
    setFilters({ question: '', status: '' })
    setAppliedFilters({})
    setSearchQuery('')
    setCurrentPage(1) // Reset to first page when filters are reset
  }

  // Handle pagination changes
  const handlePageChange = (page) => setCurrentPage(page)
  const handlePerPageValue = (perPage) => setPerPage(perPage)

  // Handle chatbot deletion
  const handleDeleteChatbot = async (id, question) => {
    if (window.confirm(`Are you sure to delete the chatbot with question: "${question}"?`)) {
      try {
        const response = await axios.delete(`${BASE_URL}/api/chatbots/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        })
        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          setChatbots((prev) => prev.filter((chatbot) => chatbot.id !== id))
        }
      } catch (error) {
        console.error('Error deleting chatbot:', error)
        alert('An error occurred while processing your request.')
      }
    }
  }

  // Fetch chatbots based on filters and search query
  const fetchChatbots = async () => {
    try {
      const response = await axios.get(`${BASE_URL}/api/chatbots`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authToken}`,
        },
        params: {
          search: searchQuery,
          ...appliedFilters,
          page: currentPage,
          per_page: perPage,
        },
      })
      setChatbots(response.data.data)
    } catch (error) {
      console.error('Error fetching chatbots:', error)
    }
  }

  useEffect(() => {
    fetchChatbots()
  }, [searchQuery, appliedFilters, currentPage, perPage])

  // Define custom filter UI for Chatbots
  const renderChatbotFilters = (filters, setFilters) => (
    <CRow className="mb-3">
      <CCol xs={12} md={6}>
        <label htmlFor="question" className="form-label">Question:</label>
        <CFormInput
          id="question"
          type="text"
          name="question"
          value={filters.question || ''}
          onChange={(e) => setFilters((prev) => ({ ...prev, question: e.target.value }))}
          placeholder="Enter question"
        />
      </CCol>
      <CCol xs={12} md={6}>
        <label htmlFor="status" className="form-label">Status:</label>
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
  )

  return (
    <>
      <CRow>
        <CCol>
          <CContainer className="mb-3">
            <CRow>
              <CCol md={8}>
                <Link to="/chatbot/create">
                  <CButton color="primary" className="me-2" size="sm">Create Chatbot</CButton>
                </Link>
              </CCol>
              <SearchAndFilter
                onSearch={setSearchQuery}
                onApplyFilters={handleApplyFilters}
                onResetFilters={handleResetFilters}
                filters={filters}
                setFilters={setFilters}
                renderFilters={renderChatbotFilters}
              />
            </CRow>
            <hr />
          </CContainer>

          <CTable hover bordered responsive="md">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell scope="col" style={{ width: '5%' }}>#</CTableHeaderCell>
                <CTableHeaderCell scope="col" style={{ width: '35%' }}>Question</CTableHeaderCell>
                <CTableHeaderCell scope="col" style={{ width: '35%' }}>Answer</CTableHeaderCell>
                <CTableHeaderCell scope="col" style={{ width: '10%' }}>Status</CTableHeaderCell>
                <CTableHeaderCell scope="col" style={{ width: '15%' }}>Controls</CTableHeaderCell>
              </CTableRow>
            </CTableHead>

            <CTableBody>
              {chatbots.length > 0 ? (
                chatbots.map((chatbot, index) => (
                  <CTableRow key={chatbot.id}>
                    <CTableDataCell style={{ width: '5%' }}>{index + 1 + (currentPage - 1) * perPage}</CTableDataCell>
                    <CTableDataCell style={{ width: '35%' }}>{chatbot.question}</CTableDataCell>
                    <CTableDataCell style={{ width: '35%' }}>{chatbot.answer}</CTableDataCell>
                    <CTableDataCell style={{ width: '10%' }}>{chatbot.status ? 'Active' : 'Inactive'}</CTableDataCell>
                    <CTableDataCell style={{ width: '15%' }}>
                      <CRow className="justify-content-center mx-0">
                        <CCol className="mx-auto d-flex justify-content-center">
                          <CTooltip content="Edit">
                            <CButton
                              color="warning"
                              size="sm"
                              onClick={() => navigate(`/chatbot/edit/${chatbot.id}`)}
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
                              onClick={() => handleDeleteChatbot(chatbot.id, chatbot.question)}
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
            apiEndpoint={`${BASE_URL}/api/chatbots`}
            authToken={authToken}
            onDataFetched={setChatbots}
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

export default Chatbot
