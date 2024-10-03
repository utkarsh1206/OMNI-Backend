// import React from 'react'
import React, { useState, useEffect } from 'react'
import axios from 'axios'
import {
  CCard,
  CCardBody,
  CCardHeader,
  CCol,
  CFormCheck,
  CRow,
  CTable,
  CTableBody,
  CTableDataCell,
  CTableHead,
  CTableHeaderCell,
  CTableRow,
} from '@coreui/react'
import { Link } from 'react-router-dom'

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

const Tables = () => {
  const linkStyle = {
    textDecoration: 'none',
    color: 'inherit',
  }

  const { posts } = PostsTable()

  return (
    <CRow>
      <CCol xs={12}>
        <CCard className="mb-4" style={{ opacity: 0.9 }}>
          <CCardHeader>
            <strong>Test Table</strong> <small>Shows a dummy data</small>
          </CCardHeader>
          <CCardBody>
            <p className="text-body-secondary small">
              {/* Use <code>hover</code> property to enable a hover state on table rows within a{' '}
              <code>&lt;CTableBody&gt;</code>. */}
              This section is used to display a Dummy Table as will be seen in the admin reports.
            </p>
            {/* <DocsExample href="components/table#hoverable-rows"> */}
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
                    {/* <CTableDataCell> <Link to={`/id/${post.id}`} style={linkStyle}>{post.id}</Link> </CTableDataCell> */}
                    <CTableDataCell style={{ width: '5%' }}>
                      <Link to={`/forms/form-control`} style={linkStyle}>
                        {post.id}
                      </Link>
                    </CTableDataCell>
                    {/* <CTableDataCell as="button">{post.id}-test</CTableDataCell> */}
                    <CTableDataCell>{post.title}</CTableDataCell>
                    <CTableDataCell>{post.body}</CTableDataCell>
                  </CTableRow>
                ))}
              </CTableBody>
            </CTable>
          </CCardBody>
        </CCard>
      </CCol>
    </CRow>
  )
}

export default Tables
