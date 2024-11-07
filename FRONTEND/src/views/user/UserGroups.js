import React, { useContext, useEffect, useState } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CCol,
  CContainer,
  CForm,
  CFormInput,
  CFormLabel,
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

const UserGroups = () => {
  const navigate = useNavigate()
  const [userGroups, setUserGroups] = useState([])
  const { authToken } = useContext(AuthContext)

  const handleDeleteUserGroup = async (id, name) => {
    if (window.confirm('Are you sure to delete ' + name + '?')) {
      console.log('pressed OK')
      try {
        let response = await axios.delete(`${BASE_URL}/api/usergroup/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          // Refetch users after deletion
          setUserGroups((prev) => prev.filter((userGroup) => userGroup.id !== id)) // Immediately update the UI
        }
      } catch (error) {
        console.error('Operation failed:', error)
        alert('An error occurred while processing your request.')
      }
    } else {
      console.log('pressed cancel')
    }
  }

  // Format date
  const formatDate = (string) => {
    const options = { year: 'numeric', month: 'long', day: 'numeric' }
    return new Date(string).toLocaleDateString([], options)
  }

  return (
    <>
      <CRow>
        <CCol xs={12}>
          <CCard className="mb-3">
            <CCardHeader>
              Search for <strong>User Groups</strong> <small>Filters will be filled later</small>
            </CCardHeader>
            <CCardBody>
              <CForm id="userGroupFilter">
                <CRow className="mb-2">
                  <CCol>
                    <CRow className="mb-2">
                      <CFormLabel htmlFor="groupID" className="col-sm-3 col-form-label">
                        {<small>User Group ID</small>}
                      </CFormLabel>
                      <CCol>
                        <CFormInput type="text" id="groupID" />
                      </CCol>
                    </CRow>
                  </CCol>
                  <CCol>
                    <CRow className="mb-2 align-middle">
                      <CFormLabel htmlFor="groupName" className="col-sm-3 col-form-label">
                        {<small> Group Name</small>}
                      </CFormLabel>
                      <CCol>
                        <CFormInput type="text" id="groupName" />
                      </CCol>
                    </CRow>
                  </CCol>
                </CRow>
                <div className="d-grid gap-2 d-md-flex justify-content-md-end">
                  <CButton type="submit" color="primary" form="userGroupFilter">
                    Submit
                  </CButton>
                </div>
              </CForm>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>
      <h6>User Groups Listing</h6>
      <hr />
      <CRow>
        <CCol>
          <CContainer>
            <CRow fluid responsive>
              <CCol md={8}>
                <Link to="/user/crt_usr_grp">
                  <CButton color="primary" className="mb-4 me-2" size="sm">
                    Create User Group
                  </CButton>
                </Link>
                <CButton
                  color="primary"
                  className="mb-4 me-2"
                  size="sm"
                  onClick={handleDeleteUserGroup}
                >
                  Delete User Group
                </CButton>
                <CButton color="primary" className="mb-4 me-2" size="sm">
                  Download CSV
                </CButton>
              </CCol>
            </CRow>
          </CContainer>
          <CTable hover bordered responsive="md">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell>
                <CTableHeaderCell scope="col">Name</CTableHeaderCell>
                <CTableHeaderCell scope="col">Campaign ID</CTableHeaderCell>
                <CTableHeaderCell scope="col">Description</CTableHeaderCell>
                <CTableHeaderCell scope="col">Status</CTableHeaderCell>
                <CTableHeaderCell scope="col">Registration Date</CTableHeaderCell>
                <CTableHeaderCell scope="col">Controls</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {userGroups.map((group) => (
                <CTableRow key={group.id}>
                  <CTableDataCell>{group.id}</CTableDataCell>
                  <CTableDataCell>{group.name}</CTableDataCell>
                  <CTableDataCell>{group.campaign_id}</CTableDataCell>
                  <CTableDataCell>{group.description}</CTableDataCell>
                  <CTableDataCell>{group.status}</CTableDataCell>
                  <CTableDataCell>{group.status}</CTableDataCell>
                  <CTableDataCell>{formatDate(group.created_at)}</CTableDataCell>
                  <CTableDataCell>
                    <CRow className="justify-content-center mx-0">
                      <CCol className="mx-auto d-flex justify-content-center">
                        <CTooltip content="Edit">
                          <CButton
                            color="warning"
                            size="sm"
                            onClick={() => navigate(`/user/edit_usergroup/${group.id}`)}
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
                            onClick={() => handleDeleteUserGroup(group.id, group.name)}
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
            apiEndpoint={`${BASE_URL}/api/usergroup`}
            authToken={authToken}
            onDataFetched={setUserGroups}
            // params={[testing]}
          />
        </CCol>
      </CRow>
    </>
  )
}

export default UserGroups
