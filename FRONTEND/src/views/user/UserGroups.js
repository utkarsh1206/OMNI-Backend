import React, { useEffect, useState } from 'react'
import {
    CButton,
    CCard,
    CCardBody,
    CCardHeader,
    CCol,
    CContainer,
    CForm,
    CFormCheck,
    CFormInput,
    CFormLabel,
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

const UserGroups = () => {
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
                    <CCard className="mb-3">
                        <CCardHeader>
                            Search for <strong>User Groups</strong> <small>Filters will be filled later</small>
                        </CCardHeader>
                        <CCardBody>
                            <CForm id='userGroupFilter'>
                                <CRow className="mb-2">
                                    <CCol>
                                        <CRow className="mb-2">
                                            <CFormLabel htmlFor="groupID" className="col-sm-3 col-form-label">
                                                {<small>User Group ID</small>}
                                            </CFormLabel>
                                            <CCol>
                                                <CFormInput
                                                    type="text"
                                                    id="groupID"
                                                />
                                            </CCol>
                                        </CRow>
                                    </CCol>
                                    <CCol>
                                        <CRow className="mb-2 align-middle">
                                            <CFormLabel htmlFor="groupName" className="col-sm-3 col-form-label">
                                                {<small> Group Name</small>}
                                            </CFormLabel>
                                            <CCol>
                                                <CFormInput
                                                    type="text"
                                                    id="groupName"
                                                />
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
                                <CButton color="primary" className="mb-4 me-2" size="sm" onClick={handleDeleteUser}>
                                    Delete User Group
                                </CButton>
                                <UserModal visible={visible} setVisible={setVisible} type={modalType} />
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

export default UserGroups
