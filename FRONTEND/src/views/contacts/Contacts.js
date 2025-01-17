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
} from '@coreui/react'
import { Link, useNavigate } from 'react-router-dom'
import { BASE_URL, PRODUCTS } from '../../config'
import { cilPencil, cilXCircle } from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../context/AuthContext'
import PaginationComponent from '../../components/PaginationComponent'
import { formatDate } from '../../utils/DateUtils'
import axios from 'axios'
import SearchAndFilter from '../../components/SearchAndFilter'

const Contacts = () => {
  const navigate = useNavigate()
  const [contacts, setContacts] = useState([])
  const { authToken } = useContext(AuthContext)
  const [accounts, setAccounts] = useState([])
  const [owners, setOwner] = useState([])

  const [selectedProduct, setSelectedProduct] = useState('')
  const [selectedAccount, setSelectedAccount] = useState('')
  const [selectedOwner, setSelectedOwner] = useState('')

  const [searchQuery, setSearchQuery] = useState('')
  const [filters, setFilters] = useState({
    product_id: '',
    account_id: '',
    owner_id: '',
  })

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
    setPerPage(perPage) // Update the current page when pagination changes
  }

  const getProductName = (product_id) => {
    const product = PRODUCTS.find((product) => String(product.id) === String(product_id))
    return product ? product.name : ''
  }

  const handleDeleteAccount = async (id) => {
    if (window.confirm('Are you sure to delete?')) {
      try {
        let response = await axios.delete(`${BASE_URL}/api/contacts/${id}`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          setContacts((prev) => prev.filter((contacts) => contacts.id !== id))
        }
      } catch (error) {
        console.error('Operation failed:', error)
        alert('An error occurred while processing your request.')
      }
    }
  }

  useEffect(() => {
    const fetchAccounts = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/api/accounts?page=all`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        setAccounts(response.data.data)
      } catch (err) {
        setError('Error fetching Accounts')
      }
    }
    fetchAccounts()
  }, [])

  const getAccountName = (account_id) => {
    const account = accounts.find((account) => String(account.id) === String(account_id))
    return account ? account.name : ''
  }

  useEffect(() => {
    const fetchOwner = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/api/users?all_user=Y`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        setOwner(response.data.data)
      } catch (err) {
        setError('Error fetching Owner')
      }
    }
    fetchOwner()
  }, [])

  const getOwnerName = (owner_id) => {
    const owner = owners.find((owner) => String(owner.id) === String(owner_id))
    return owner ? `${owner.first_name} ${owner.last_name}` : ''
  }

  const handleProductChange = (e) => {
    setSelectedProduct(e.target.value)
  }

  const handleAccountChange = (e) => {
    setSelectedAccount(e.target.value)
  }

  const handleOwnerChange = (e) => {
    setSelectedOwner(e.target.value)
  }

  // const handleSearch = async () => {
  //   try {
  //     const response = await axios.get(`${BASE_URL}/api/contacts`, {
  //       headers: { Authorization: `Bearer ${authToken}` },
  //       params: {
  //         search: searchQuery,
  //         product_id: selectedProduct,
  //         account_id: selectedAccount,
  //         owner_id: selectedOwner,
  //         page: currentPage,
  //         per_page: perPage
  //       }
  //     })
  //     setContacts(response.data.data)
  //   } catch (err) {
  //     console.error('Error fetching contacts', err)
  //   }
  // }

  // Define custom filter card for Users
  const renderContactFilters = (filters, setFilters) => (
    <CRow style={{ display: 'block ruby', alignItems: 'center' }}>
      <CCol md="3" className="mb-3">
        <CFormLabel htmlFor="product_id">Product</CFormLabel>
        <CFormSelect
          id="product_id"
          name="product_id"
          value={filters.product_id}
          onChange={(e) => setFilters((prev) => ({ ...prev, product_id: e.target.value }))}
        >
          <option value="">Select Product</option>
          {PRODUCTS.map((product) => (
            <option key={product.name} value={product.id}>
              {product.name}
            </option>
          ))}
        </CFormSelect>
      </CCol>

      <CCol md="3" className="mb-3">
        <CFormLabel htmlFor="account_id">Account</CFormLabel>
        <CFormSelect
          id="account_id"
          name="account_id"
          value={filters.account_id}
          onChange={(e) => setFilters((prev) => ({ ...prev, account_id: e.target.value }))}
        >
          <option value="">Select Account</option>
          {accounts.map((account) => (
            <option key={account.id} value={account.id}>
              {account.name}
            </option>
          ))}
        </CFormSelect>
      </CCol>

      <CCol md="3" className="mb-3">
        <CFormLabel htmlFor="owner_id">Owner</CFormLabel>
        <CFormSelect
          id="owner_id"
          name="owner_id"
          value={filters.owner_id}
          onChange={(e) => setFilters((prev) => ({ ...prev, owner_id: e.target.value }))}
        >
          <option value="">Select Owner</option>
          {owners.map((owner) => (
            <option key={owner.id} value={owner.id}>
              {owner.first_name} {owner.last_name}
            </option>
          ))}
        </CFormSelect>
      </CCol>
      {/* <CCol>
        <CButton
          type="button"
          color="primary"
          size="sm"
          onClick={() => handleSearch()}
          className="d-inline-block"
        >
          Search
        </CButton>
      </CCol> */}
    </CRow>
  )

  return (
    <CRow>
      <CCol>
        <CContainer>
          <CRow className="mb-3 align-items-center">
            <CCol className="d-flex">
              <Link to="/contacts/create_contacts">
                <CButton color="primary" className="me-2" size="sm">
                  Create Contact
                </CButton>
              </Link>
              <CButton color="primary" className="me-2" size="sm">
                Delete Contact
              </CButton>
            </CCol>

            <SearchAndFilter
              onSearch={setSearchQuery}
              onApplyFilters={handleApplyFilters}
              onResetFilters={handleResetFilters} // Callback for reset filters
              filters={filters}
              setFilters={setFilters}
              renderFilters={renderContactFilters}
            />
          </CRow>
        </CContainer>
        <hr />
        <CTable hover bordered responsive="md">
          <CTableHead color="light">
            <CTableRow>
              <CTableHeaderCell scope="col">#</CTableHeaderCell>
              <CTableHeaderCell scope="col">Product</CTableHeaderCell>
              <CTableHeaderCell scope="col">Account</CTableHeaderCell>
              <CTableHeaderCell scope="col">Name</CTableHeaderCell>
              <CTableHeaderCell scope="col">Email</CTableHeaderCell>
              <CTableHeaderCell scope="col">Mobile</CTableHeaderCell>
              <CTableHeaderCell scope="col">Owner</CTableHeaderCell>
              <CTableHeaderCell scope="col">Created On</CTableHeaderCell>
              <CTableHeaderCell scope="col">Updated On</CTableHeaderCell>
              <CTableHeaderCell scope="col">Controls</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            {contacts.map((contact, index) => (
              <CTableRow key={contact.id}>
                <CTableDataCell>{index + 1}</CTableDataCell>
                <CTableDataCell>{getProductName(contact.product_id)}</CTableDataCell>
                <CTableDataCell>{getAccountName(contact.account_id)}</CTableDataCell>
                <CTableDataCell>
                  {contact.name_prefix} {contact.first_name} {contact.last_name}
                </CTableDataCell>
                <CTableDataCell>{contact.email ? contact.email : ''}</CTableDataCell>
                <CTableDataCell>{contact.mobile}</CTableDataCell>
                <CTableDataCell>{getOwnerName(contact.owner_id)}</CTableDataCell>
                <CTableDataCell>{formatDate(contact.created_at)}</CTableDataCell>
                <CTableDataCell>{formatDate(contact.updated_at)}</CTableDataCell>
                <CTableDataCell>
                  <div style={{ display: 'flex', justifyContent: 'center', gap: '8px' }}>
                    <CTooltip content="Edit">
                      <CButton
                        color="warning"
                        size="sm"
                        onClick={() => navigate(`/contacts/edit/${contact.id}`)}
                      >
                        <CIcon icon={cilPencil} />
                      </CButton>
                    </CTooltip>
                    <CTooltip content="Delete">
                      <CButton
                        color="danger"
                        size="sm"
                        onClick={() => handleDeleteAccount(contact.id)}
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
          apiEndpoint={`${BASE_URL}/api/contacts`}
          authToken={authToken}
          onDataFetched={setContacts}
          filters={appliedFilters}
          search={searchQuery}
          onPageChange={handlePageChange}
          perPageValue={handlePerPageValue}
        />
      </CCol>
    </CRow>
  )
}

export default Contacts
