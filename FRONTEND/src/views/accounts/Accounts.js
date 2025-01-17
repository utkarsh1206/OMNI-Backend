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
import { BASE_URL, CAMPAIGNS, PRODUCTS } from '../../config'
import { cilPencil, cilXCircle } from '@coreui/icons'
import CIcon from '@coreui/icons-react'
import { AuthContext } from '../../context/AuthContext'
import PaginationComponent from '../../components/PaginationComponent'
import { formatDate } from '../../utils/DateUtils'
import axios from 'axios'
import SearchAndFilter from '../../components/SearchAndFilter'

const Accounts = () => {
  const navigate = useNavigate()
  const [accounts, setAccounts] = useState([])
  const [owners, setOwners] = useState([])
  const { authToken } = useContext(AuthContext)
  const [selectedProduct, setSelectedProduct] = useState('')
  const [selectedCampaign, setSelectedCampaign] = useState('')
  const [selectedOwner, setSelectedOwner] = useState('')

  const [searchQuery, setSearchQuery] = useState('')
  const [filters, setFilters] = useState({
    product_id: '',
    campaign_id: '',
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
        setOwners(response.data.data)
      } catch (err) {
        // setError('Error fetching Owner')
      }
    }
    fetchOwner()
  }, [authToken])

  const getOwnerName = (owner_id) => {
    const owner = owners.find((owner) => String(owner.id) === String(owner_id))
    return owner ? `${owner.first_name} ${owner.last_name}` : ''
  }

  const handleDeleteAccount = async (id, name) => {
    if (window.confirm('Are you sure to delete ' + name + '?')) {
      try {
        let response = await axios.delete(`${BASE_URL}/api/accounts/${id}`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          setAccounts((prev) => prev.filter((account) => account.id !== id))
        }
      } catch (error) {
        alert('An error occurred while processing your request.')
      }
    }
  }

  const getCampaignName = (campaign_id) => {
    const campaign = CAMPAIGNS.find((campaign) => String(campaign.id) === String(campaign_id))
    return campaign ? campaign.name : ''
  }

  const getProductName = (product_id) => {
    const product = PRODUCTS.find((product) => String(product.id) === String(product_id))
    return product ? product.name : ''
  }

  // const handleProductChange = (e) => {
  //   setSelectedProduct(e.target.value)
  // }

  // const handleCampaignChange = (e) => {
  //   setSelectedCampaign(e.target.value)
  // }

  // const handleOwnerChange = (e) => {
  //   setSelectedOwner(e.target.value)
  // }

  // const handleSearch = async () => {
  //   try {
  //     const response = await axios.get(`${BASE_URL}/api/accounts`, {
  //       headers: { Authorization: `Bearer ${authToken}` },
  //       params: {
  //         search: searchQuery,
  //         product_id: selectedProduct,
  //         campaign_id: selectedCampaign,
  //         owner_id: selectedOwner,
  //         page: currentPage,
  //         per_page: perPage
  //       }
  //     })
  //     setAccounts(response.data.data)
  //   } catch (err) {
  //     console.error('Error fetching Accounts', err)
  //   }
  // }

  const renderAccountFilters = (filters, setFilters) => (
    <CRow style={{ display: 'block ruby', alignItems: 'center' }}>
      <CCol md="3" className="mb-3">
        <CFormLabel htmlFor="product_id">Product</CFormLabel>
        <CFormSelect
          id="product_id"
          name="product_id"
          // value={selectedProduct}
          // onChange={handleProductChange}
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
        <CFormLabel htmlFor="campaign_id">Campaign</CFormLabel>
        <CFormSelect
          id="campaign_id"
          name="campaign_id"
          // value={selectedCampaign}
          // onChange={handleCampaignChange}
          value={filters.campaign_id}
          onChange={(e) => setFilters((prev) => ({ ...prev, campaign_id: e.target.value }))}
        >
          <option value="">Select Campaign</option>
          {CAMPAIGNS.map((campaign) => (
            <option key={campaign.name} value={campaign.id}>
              {campaign.name}
            </option>
          ))}
        </CFormSelect>
      </CCol>

      <CCol md="3" className="mb-3">
        <CFormLabel htmlFor="owner_id">Owner</CFormLabel>
        <CFormSelect
          id="owner_id"
          name="owner_id"
          // value={selectedOwner}
          // onChange={handleOwnerChange}
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
              <Link to="/accounts/create_accounts">
                <CButton color="primary" className="me-2" size="sm">
                  Create Account
                </CButton>
              </Link>
              <CButton color="primary" className="me-2" size="sm">
                Delete Account
              </CButton>
            </CCol>
            <SearchAndFilter
              onSearch={setSearchQuery}
              onApplyFilters={handleApplyFilters}
              onResetFilters={handleResetFilters} // Callback for reset filters
              filters={filters}
              setFilters={setFilters}
              renderFilters={renderAccountFilters} // Custom filter UI for users
            />
          </CRow>
        </CContainer>
        <hr />
        <CTable hover bordered responsive="md">
          <CTableHead color="light">
            <CTableRow>
              <CTableHeaderCell scope="col">#</CTableHeaderCell>
              <CTableHeaderCell scope="col">Product</CTableHeaderCell>
              <CTableHeaderCell scope="col">Campaign</CTableHeaderCell>
              <CTableHeaderCell scope="col">Name</CTableHeaderCell>
              <CTableHeaderCell scope="col">Owner</CTableHeaderCell>
              <CTableHeaderCell scope="col">Website</CTableHeaderCell>
              <CTableHeaderCell scope="col">Mobile Number</CTableHeaderCell>
              <CTableHeaderCell scope="col">Created On</CTableHeaderCell>
              <CTableHeaderCell scope="col">Updated On</CTableHeaderCell>
              <CTableHeaderCell scope="col">Controls</CTableHeaderCell>
            </CTableRow>
          </CTableHead>
          <CTableBody>
            {accounts.map((account, index) => (
              <CTableRow key={account.id}>
                <CTableDataCell>{index + 1}</CTableDataCell>
                <CTableDataCell>{getProductName(account.product_id)}</CTableDataCell>
                <CTableDataCell>{getCampaignName(account.campaign_id)}</CTableDataCell>
                <CTableDataCell>{account.name}</CTableDataCell>
                <CTableDataCell>{getOwnerName(account.owner_id)}</CTableDataCell>
                <CTableDataCell>{account.website}</CTableDataCell>
                <CTableDataCell>{account.mobile}</CTableDataCell>
                <CTableDataCell>{formatDate(account.created_at)}</CTableDataCell>
                <CTableDataCell>{formatDate(account.updated_at)}</CTableDataCell>
                <CTableDataCell>
                  <div style={{ display: 'flex', justifyContent: 'center', gap: '8px' }}>
                    <CTooltip content="Edit">
                      <CButton
                        color="warning"
                        size="sm"
                        onClick={() => navigate(`/accounts/edit/${account.id}`)}
                      >
                        <CIcon icon={cilPencil} />
                      </CButton>
                    </CTooltip>
                    <CTooltip content="Delete">
                      <CButton
                        color="danger"
                        size="sm"
                        onClick={() => handleDeleteAccount(account.id, account.name)}
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
          apiEndpoint={`${BASE_URL}/api/accounts`}
          authToken={authToken}
          onDataFetched={setAccounts}
          filters={appliedFilters}
          search={searchQuery}
          onPageChange={handlePageChange}
          perPageValue={handlePerPageValue}
        />
      </CCol>
    </CRow>
  )
}

export default Accounts
