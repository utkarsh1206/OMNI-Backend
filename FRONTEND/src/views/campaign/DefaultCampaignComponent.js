import { CCol, CFormLabel, CFormSelect } from '@coreui/react'
import React, { useContext, useEffect, useState } from 'react'
import { BASE_URL, CAMPAIGNS } from './../../config'
import axios from 'axios'
import { AuthContext } from '../../context/AuthContext'

function DefaultCampaignComponent({ formData, handleChange }) {
  const [departments, setDepartments] = useState([])
  const [loadingDepartments, setLoadingDepartments] = useState(false)
  const { authToken } = useContext(AuthContext)
  // Fetch Campaigns on component mount
  useEffect(() => {
    const fetchDepartments = async () => {
      setLoadingDepartments(true)
      try {
        const response = await axios.get(`${BASE_URL}/api/departments?page=all`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        setDepartments(response.data.data)
        setLoadingDepartments(false)
      } catch (err) {
        // setError('Error fetching Departments')
        setLoadingDepartments(false)
      }
    }
    fetchDepartments()
  }, [])

  return (
    <CCol md="4" className="mb-3">
      <CFormLabel htmlFor="campaign_id">
        Department <span style={{ color: 'red' }}>*</span>
      </CFormLabel>
      <CFormSelect
        id="campaign_id"
        name="campaign_id"
        value={formData.campaign_id}
        onChange={handleChange}
      >
        <option value="" disabled>
          Select Department
        </option>
        {departments.map((department) => (
          <option key={department.name} value={department.id}>
            {department.name}
          </option>
        ))}
      </CFormSelect>
    </CCol>
  )
}

export default DefaultCampaignComponent
