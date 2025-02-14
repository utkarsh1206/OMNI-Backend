import { CCol, CFormLabel, CFormSelect } from '@coreui/react'
import React, { useContext, useEffect, useState } from 'react'
// import { PRODUCTS } from './../../config'
import axios from 'axios'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'

function DefaultProductsComponent({ formData, handleChange }) {
  const [products, setProducts] = useState([])
  const [loadingProducts, setLoadingProducts] = useState(false)
  const [error, setError] = useState('')
  const { authToken } = useContext(AuthContext)

  // Fetch Products on component mount
  useEffect(() => {
    const fetchProducts = async () => {
      setLoadingProducts(true)
      try {
        const response = await axios.get(`${BASE_URL}/api/products?page=all`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        setProducts(response.data.data)
        setLoadingProducts(false)
      } catch (err) {
        setError('Error fetching Products' + 'err.response.message')
        setLoadingProducts(false)
      }
    }
    fetchProducts()
  }, [])

  return (
    <CCol md="4" className="mb-3">
      <CFormLabel htmlFor="product_id">
        Product <span style={{ color: 'red' }}>*</span>
      </CFormLabel>
      <CFormSelect
        id="product_id"
        name="product_id"
        value={formData.product_id}
        onChange={handleChange}
      >
        <option value="" disabled>
          Select Product
        </option>
        {products.map((product) => (
          <option key={product.name} value={product.id}>
            {product.name}
          </option>
        ))}
        {/* Add more Product options as needed */}
      </CFormSelect>
    </CCol>
  )
}

export default DefaultProductsComponent
