import React, { useState, useEffect, useContext } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CForm,
  CFormLabel,
  CFormTextarea,
  CFormInput,
  CRow,
  CCol,
  CFormSelect,
} from '@coreui/react'
import axios from 'axios'
import { useNavigate, useParams } from 'react-router-dom'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'


const FormGroup = ({ label, name, value, onChange, required, type, disabled, textarea }) => (
  <CCol md="6" className="mb-3">
    <CFormLabel htmlFor={name}>
      {label} {required && <span style={{ color: 'red' }}>*</span>}
    </CFormLabel>
    {textarea ? (
      <CFormTextarea
        id={name}
        name={name}
        value={value}
        onChange={onChange}
        rows="3"  // Adds a 3-line height for the textarea
        required={required}
        disabled={disabled}
      />
    ) : (
      <CFormInput
        type={type}
        id={name}
        name={name}
        value={value}
        onChange={onChange}
        required={required}
        disabled={disabled}
      />
    )}
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
    status: 'true',
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
            status: productData.status ? 'true' : 'false',
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
      [name]: name === "status" ? value === "true" : value,
    }))
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    try {
      const submissionData = {
        ...formData,
        status: formData.status === 'true',
      }
      let response
      if (isEditMode) {
        // Update existing product
        response = await axios.put(`${BASE_URL}/api/products/${id}`, submissionData, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        })
      } else {
        response = await axios.post(`${BASE_URL}/api/products`, submissionData, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        })
      }

      if (response.status === 201 || response.status === 200) {
        alert(
          isEditMode ? `Product updated successfully.` : `Product created successfully.`
        )
        navigate('/product')

      }
    } catch (error) {
      console.error('Operation failed:', error)
      if (error.response && error.response.data && error.response.data.errors) {
        const errorMessages = Object.values(error.response.data.errors).flat().join('\n')
        alert(`Validation failed:\n${errorMessages}`)
      } else {
        alert(error.response?.data?.message || 'An error occurred')
      }
    }
  }

  const handleCancel = () => {
    setFormData({
      name: '',
      description: '',
      status: 'true',
    })
    navigate('/product')
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
                <CCol md="6" className="mb-3">
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
                    <option value='true'>Active</option>
                    <option value='false'>Inactive</option>
                  </CFormSelect>
                </CCol>
              </CRow>
              <CRow>
                <FormGroup
                  label="Description"
                  name="description"
                  //type="text"
                  value={formData.description}
                  onChange={handleChange}
                  textarea
                  required
                />
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