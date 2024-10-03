import React, { useEffect, useState } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CCol,
  CContainer,
  CFormCheck,
  CRow,
  CTable,
  CTableBody,
  CTableDataCell,
  CTableHead,
  CTableHeaderCell,
  CTableRow,
} from '@coreui/react'
import axios from 'axios'
import { Link } from 'react-router-dom'
import UserModal from './UserModal'

const PostsTable = () => {
  const [posts, setPosts] = useState([])

  useEffect(() => {
    axios
      .get('https://jsonplaceholder.typicode.com/posts')
      .then((response) => {
        setPosts(response.data)
      })
      .catch((error) => {
        console.error('There was an error fetching the posts!', error)
      })
  }, [])
  return { posts }
}

const Users = () => {
  const { posts } = PostsTable()

  const linkStyle = {
    textDecoration: 'none',
    color: 'inherit',
  }

  const [visible, setVisible] = useState(false)
  const [modalType, setModalType] = useState('')

  const handleCreateUser = () => {
    setModalType('create')
    setVisible(true)
  }

  const handleDeleteUser = () => {
    setModalType('delete')
    setVisible(true)
  }

  return (
    <>
      <CRow>
        <CCol xs={12}>
          <CCard className="mb-4">
            <CCardHeader>
              Search for <strong>Users</strong> <small>Filters will be filled later</small>
            </CCardHeader>
            <CCardBody>This is a test body</CCardBody>
          </CCard>
        </CCol>
      </CRow>
      <hr />
      <CRow>
        <CCol>
          <CContainer>
            <CRow fluid responsive>
              <CCol md={8}>
                <CButton color="primary" className="mb-4 me-2" size="sm" onClick={handleCreateUser}>
                  Create User
                </CButton>
                <CButton color="primary" className="mb-4 me-2" size="sm" onClick={handleDeleteUser}>
                  Delete User
                </CButton>
                <UserModal visible={visible} setVisible={setVisible} type={modalType} />
                <CButton color="primary" className="mb-4 me-2" size="sm">
                  Download CSV
                </CButton>
              </CCol>
              <CCol md={4} className="d-flex flex-row-reverse">
                <CButton color="primary" className="mb-4 me-2" size="sm">
                  Bulk User Creation
                </CButton>
              </CCol>
            </CRow>
          </CContainer>
          <CTable hover bordered responsive="md">
            <CTableHead color="light">
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell>
                <CTableHeaderCell scope="col">Id</CTableHeaderCell>
                <CTableHeaderCell scope="col">Title</CTableHeaderCell>
                <CTableHeaderCell scope="col">Body</CTableHeaderCell>
              </CTableRow>
            </CTableHead>
            <CTableBody>
              {posts.map((post) => (
                <CTableRow key={post.id}>
                  <CTableDataCell style={{ width: '5%' }}>
                    <CFormCheck />
                    &nbsp;{post.userId}
                  </CTableDataCell>
                  <CTableDataCell style={{ width: '5%' }}>
                    <Link to={`/forms/form-control`} style={linkStyle}>
                      {post.id}
                    </Link>
                  </CTableDataCell>
                  <CTableDataCell>{post.title}</CTableDataCell>
                  <CTableDataCell>{post.body}</CTableDataCell>
                </CTableRow>
              ))}
            </CTableBody>
          </CTable>
        </CCol>
      </CRow>
    </>
  )
}

export default Users
