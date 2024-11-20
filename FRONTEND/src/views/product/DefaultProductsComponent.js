import { CCol, CFormLabel, CFormSelect } from '@coreui/react'
import React, { useEffect } from 'react'

function DefaultProductsComponent({ formData, handleChange }) {

  // const [ products, setProducts ] = useState([])
  // const [loadingProducts, setLoadingProducts] = useState(false)
  //  // Fetch Products on component mount
  //  useEffect(() => {
  //   const fetchProducts = async () => {
  //     setLoadingProducts(true)
  //     try {
  //       const response = await axios.get(`${BASE_URL}/api/Products?page=all`)
  //       setProducts(response.data.data)
  //       setLoadingProducts(false)
  //     } catch (err) {
  //       setError('Error fetching Products')
  //       setLoadingProducts(false)
  //     }
  //   }
  //   fetchProducts()
  // }, [])
  
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
        <option key="Hosted" value="1">
          Hosted
        </option>
        <option key="Neox" value="2">
          Neox
        </option>
        <option key="OMNI" value="3">
          OMNI
        </option>
        {/* Add more Product options as needed */}
      </CFormSelect>
    </CCol>
  )
}

export default DefaultProductsComponent