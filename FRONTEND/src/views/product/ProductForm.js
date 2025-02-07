import React, { useState, useEffect, useContext } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CForm,
  CFormLabel,
  CFormInput,
  CRow,
  CCol,
  CFormSelect,
} from '@coreui/react'
import axios from 'axios'
import { useNavigate, useParams } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'

const FormGroup = ({ label, name, value, onChange, required, type, disabled }) => (
  <CCol md="4" className="mb-3">
    <CFormLabel htmlFor={name}>
      {label} {required && <span style={{ color: 'red' }}>*</span>}
    </CFormLabel>
    <CFormInput
      type={type}
      id={name}
      name={name}
      value={value}
      onChange={onChange}
      required={required}
      disabled={disabled}
    />
  </CCol>
)

const ProductForm = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const isEditMode = Boolean(id)
  const { authToken } = useContext(AuthContext)

  const [formData, setFormData] = useState({
    name: '',
    description: '',
    status: 'active',  // Set default status to 'active'
  })

  useEffect(() => {
    if (isEditMode) {
      axios
        .get(`${BASE_URL}/api/products/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        })
        .then((response) => {
          const productData = response.data.data
          setFormData({
            name: productData.name,
            description: productData.description,
            status: productData.status ? 'active' : 'inactive',  // Set status based on product data
          })
        })
        .catch((error) => {
          console.error('Error fetching Product data:', error)
          alert('Failed to fetch Product data.')
        })
    }
  }, [id, isEditMode, authToken])

  const handleChange = (e) => {
    const { name, value } = e.target
    setFormData((prevData) => ({
      ...prevData,
      [name]: value,
    }))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    try {
      let response
      if (isEditMode) {
        // Update existing product
        response = await axios.put(`${BASE_URL}/api/products/${id}`, formData, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        })
      } else {
        response = await axios.post(`${BASE_URL}/api/products`, formData, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        })
      }

      if (response.status === 200) {
        alert(response.data.message + (isEditMode ? `\nProduct updated successfully.` : `\nProduct created successfully.`))
        navigate('/products') // Redirect to Product list page after success
      }
      handleCancel()
    } catch (error) {
      console.error('Operation failed:', error)
      alert(error.response.data.message)
    }
  }

  const handleCancel = () => {
    setFormData({
      name: '',
      description: '',
      status: 'active',  // Reset status to default 'active'
    })
    navigate('/Product') // Redirect to Product list page if cancelling
  }

  return (
    <CForm onSubmit={handleSubmit}>
      {/* Basic Details Section */}
      <CRow>
        <CCol xs="12" md="12">
          <CCard>
            <CCardHeader>{isEditMode ? 'Edit Product' : 'Create Product'}</CCardHeader>
            <CCardBody>
              <CRow>
                <FormGroup
                  label="Product Name"
                  name="name"
                  type="text"
                  value={formData.name}
                  onChange={handleChange}
                  required={!isEditMode}
                  disabled={isEditMode}
                />
                <FormGroup
                  label="Description"
                  name="description"
                  type="text"
                  value={formData.description}
                  onChange={handleChange}
                  required
                />
                <CCol md="4" className="mb-3">
                  <CFormLabel htmlFor="status">
                    Status <span style={{ color: 'red' }}>*</span>
                  </CFormLabel>
                  <CFormSelect
                    id="status"
                    name="status"
                    value={formData.status}
                    onChange={handleChange}
                    required
                  >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                  </CFormSelect>
                </CCol>
              </CRow>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      {/* Buttons */}
      <CRow className="mt-2 mb-4">
        <CCol xs="12" md="12" className="d-flex justify-content-end">
          <CButton type="submit" color="primary" className="me-2">
            {isEditMode ? 'Update' : 'Submit'}
          </CButton>
          <CButton type="button" color="secondary" onClick={handleCancel}>
            Cancel
          </CButton>
        </CCol>
      </CRow>
    </CForm>
  )
}

export default ProductForm