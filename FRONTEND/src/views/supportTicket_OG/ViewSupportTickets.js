import React, { useContext, useEffect, useState } from 'react'
import {
  CBadge,
  CButton,
  CButtonGroup,
  CCard,
  CCardBody,
  CCardHeader,
  CCardTitle,
  CCol,
  CContainer,
  CHeader,
  CRow,
  CTable,
  CTableBody,
  CTableDataCell,
  CTableHead,
  CTableHeaderCell,
  CTableRow,
  CTooltip,
  CCardText,
  CFormLabel,
  CFormTextarea,
  CCardFooter,
  CImage,
} from '@coreui/react'
import { Link, useNavigate, useParams } from 'react-router-dom'
import { BASE_URL, PRIORITY_TO_COLOR, STATUS_TO_COLOR, TYPE_TO_COLOR } from '../../config'
import {
  cilBook,
  cilBuilding,
  cilClipboard,
  cilEnvelopeOpen,
  cilMap,
  cilMobile,
  cilPencil,
  cilTablet,
  cilTag,
  cilTransfer,
  cilWindowRestore,
  cilXCircle,
} from '@coreui/icons'
import CIcon, { CIconSvg } from '@coreui/icons-react'
import { AuthContext } from '../../context/AuthContext'
import PaginationComponent from '../../components/PaginationComponent' // import the reusable pagination component
import { formatDate, formatDateWithTime } from '../../utils/DateUtils'
import axios from 'axios'
import { cilPhone, cilFire, cilUser } from '@coreui/icons'
// import TicketActivity from './TicketActivity'

const ViewSupportTickets = () => {
  const { id } = useParams()
  const navigate = useNavigate()
  const [tickets, setTickets] = useState([])
  const { authToken, brandingInfo } = useContext(AuthContext)
  const [sideMenu, setSideMenu] = useState()

  useEffect(() => {
    // Fetch support Ticket data to display
    axios
      .get(`${BASE_URL}/api/supportticket/${id}`, {
        headers: {
          'Content-Type': 'application/json',
          'Access-Control-Allow-Origin': '*',
          Authorization: `Bearer ${authToken}`,
        },
      })
      .then((response) => {
        setTickets(response.data.data)
        document.title = 'Support Ticket - ' + response.data.data.id
      })
      .catch((error) => {
        console.error('Error fetching Support Ticket data:', error)
        alert('Failed to fetch Support Ticket data.')
      })
    return () => {
      document.title = brandingInfo.title // Set back to the default title
    }
  }, [id])

  const handleDeleteTicket = async (id, username) => {
    if (window.confirm('Are you sure to delete ' + username + '?')) {
      try {
        let response = await axios.delete(`${BASE_URL}/api/supportticket/${id}`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        })

        if (response.status === 200 || response.status === 204) {
          alert(response.data.message)
          // Optionally, refetch the data
          setTickets((prev) => prev.filter((tenant) => tenant.id !== id)) // Immediately update the UI
        }
      } catch (error) {
        console.error('Operation failed:', error)
        alert('An error occurred while processing your request.')
      }
    }
  }

  const timelineData = [
    {
      icon: cilUser,
      badgeColor: 'primary',
      title: 'Ticket Created',
      description: 'Santi contacted for the first time',
      date: 'Tue, 13 Dec 2023',
    },
    {
      icon: cilFire,
      badgeColor: 'danger',
      title: 'Type Changed',
      description: 'Changed Ticket Type to Incident',
      date: 'Tue, 13 Dec 2023',
    },
    {
      icon: cilPhone,
      badgeColor: 'info',
      title: 'Call Scheduled',
      description: 'Going on Call with +1 678-908-456',
      date: 'Tue, 13 Dec 2023',
    },
  ]

  const handleSideMenu = (id) => {
    console.log(`Button clicked with ID: ${id}`)
    if (sideMenu == id) {
      setSideMenu(null)
    } else {
      setSideMenu(id)
    }
  }

  return (
    <>
      <CContainer className="px-4">
        <CRow xs={{ gutterX: 5 }}>
          {/* Left (Main) Panel */}
          <CCol md={8} className="p-2">
            {/* TIcket Details Card */}
            <CCard className="mb-3">
              <CCardHeader>
                <CCardTitle>
                  Support Ticket Details -{' '}
                  <small>{tickets.assign_to_id} (Assigned to username)</small> -{' '}
                  <CBadge color={tickets.default_status == 'Unassigned' ? 'warning' : 'success'}>
                    {tickets.default_status == 'Unassigned' ? 'Unassigned' : 'Assigned'}
                  </CBadge>
                </CCardTitle>
              </CCardHeader>
              <CCardBody>
                <CRow>
                  <CCol sm={2} md={3} className="align-content-center text-center">
                    {/* {!brandingInfo?.tenant_logo ? (
                      <> */}
                    <CIconSvg size="9xl">
                      {/* <svg width="800px" height="800px" viewBox="0 0 1024 1024" class="icon"  version="1.1" xmlns="http://www.w3.org/2000/svg"><path d="M512 661.3c-117.6 0-213.3-95.7-213.3-213.3S394.4 234.7 512 234.7 725.3 330.4 725.3 448 629.6 661.3 512 661.3z m0-341.3c-70.6 0-128 57.4-128 128s57.4 128 128 128 128-57.4 128-128-57.4-128-128-128z" fill="#5F6379" /><path d="M837 862.9c-15.7 0-30.8-8.7-38.2-23.7C744.3 729.5 634.4 661.3 512 661.3s-232.3 68.1-286.8 177.9c-10.5 21.1-36.1 29.7-57.2 19.2s-29.7-36.1-19.2-57.2C217.8 662.3 357 576 512 576s294.2 86.3 363.2 225.2c10.5 21.1 1.9 46.7-19.2 57.2-6.1 3-12.6 4.5-19 4.5z" fill="#5F6379" /><path d="M512 1002.7c-270.6 0-490.7-220.1-490.7-490.7S241.4 21.3 512 21.3s490.7 220.1 490.7 490.7-220.1 490.7-490.7 490.7z m0-896c-223.5 0-405.3 181.8-405.3 405.3S288.5 917.3 512 917.3 917.3 735.5 917.3 512 735.5 106.7 512 106.7z" fill="#3688FF" /></svg> */}
                      <svg fill="#000000" viewBox="0 0 30 30" xmlns="http://www.w3.org/2000/svg">
                        <g id="SVGRepo_bgCarrier" stroke-width="0"></g>
                        <g
                          id="SVGRepo_tracerCarrier"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        ></g>
                        <g id="SVGRepo_iconCarrier">
                          <path d="M15 6c-1.55 0-2.814.786-3.666 1.78C10.482 8.778 10 9.975 10 11v2.5c0 .667.334 1.152.584 1.527.25.375.416.64.416.973v1c0 .61-.062 1.174-.748 1.566l-3.5 2c-.8.457-1.272.9-1.518 1.416C4.99 22.498 5 23.002 5 23.5c0 .67 1 .66 1 0 0-.5.008-.818.137-1.088.128-.27.406-.575 1.11-.978l3.5-2C11.813 18.826 12 17.734 12 17v-1c0-.667-.334-1.152-.584-1.527-.25-.375-.416-.64-.416-.973V11c0-.69.387-1.743 1.094-2.568C12.8 7.606 13.784 7 15 7c1.215 0 2.2.606 2.906 1.432C18.613 9.257 19 10.312 19 11v2.5c0 .333-.166.598-.416.973-.25.375-.584.86-.584 1.527v1c0 .735.188 1.826 1.252 2.434l3.5 2c.705.403.983.708 1.11.978.13.27.138.587.138 1.088 0 .67 1 .672 1 0 0-.5.01-1.002-.234-1.518-.246-.515-.72-.96-1.518-1.416l-3.5-2C19.062 18.174 19 17.61 19 17v-1c0-.333.166-.598.416-.973.25-.375.584-.86.584-1.527V11c0-1.026-.482-2.223-1.334-3.22C17.814 6.787 16.55 6 15 6zm0-6C6.722 0 0 6.722 0 15c0 8.278 6.722 15 15 15 8.278 0 15-6.722 15-15 0-8.278-6.722-15-15-15zm0 1c7.738 0 14 6.262 14 14s-6.262 14-14 14S1 22.738 1 15 7.262 1 15 1z"></path>
                        </g>
                      </svg>
                    </CIconSvg>
                    {/* </>
                    ) : (
                      <CImage src={brandingInfo?.tenant_logo}></CImage>
                      // Will Ideally Be the Account Logo
                    )} */}
                  </CCol>
                  <CCol>
                    <h6>
                      #{tickets.id} - {formatDateWithTime(tickets.created_at)}
                    </h6>
                    <h5>
                      <Link to={`#`}>{tickets.contact_id}</Link>
                    </h5>
                    <p>
                      <CIcon icon={cilBuilding} size="lg" />{' '}
                      <Link to={`#`}>
                        <strong>{tickets.account_id}test</strong>
                      </Link>
                    </p>
                    <p>
                      <CIcon icon={cilMobile} size="lg" /> (Contact's mobile number)
                    </p>
                    <p>
                      <CIcon icon={cilMap} size="lg" /> (Contact's Address)
                    </p>
                    <strong>Subject : </strong> {tickets.subject}
                    <CRow className="my-3">
                      <CCol sm={4}>
                        <strong>Type: </strong>{' '}
                        <CBadge color={TYPE_TO_COLOR[tickets.default_type] || 'secondary'}>
                          {tickets.default_type}
                        </CBadge>
                      </CCol>
                      <CCol sm={4}>
                        <strong>Priority :</strong>{' '}
                        <CBadge color={PRIORITY_TO_COLOR[tickets.default_priority] || 'secondary'}>
                          {tickets.default_priority}
                        </CBadge>
                      </CCol>
                      <CCol md={4}>
                        <strong>Status :</strong>{' '}
                        <CBadge color={STATUS_TO_COLOR[tickets.default_status] || 'secondary'}>
                          {tickets.default_status}
                        </CBadge>
                      </CCol>
                    </CRow>
                  </CCol>
                </CRow>
                <CRow className="mb-4">
                  <h6>Attachments:</h6>
                </CRow>
              </CCardBody>
            </CCard>

            {/* Ticket Description Card */}
            <CCard>
              <CCardHeader>
                <CCardTitle>Ticket Description</CCardTitle>
              </CCardHeader>
              <CCardBody></CCardBody>
            </CCard>
          </CCol>

          {/* Right Panel */}
          <CCol className="p-2">
            {/* PlaceHolder1 */}
            <CCard className="mb-3">
              {/* <CCardHeader></CCardHeader> */}
              <CCardBody>
                <CRow className="d-flex justify-content-around">
                  <CCol className="text-center" onClick={() => handleSideMenu(1)}>
                    {/* <CIcon size='xl' icon={cilTablet} /> */}
                    <CTooltip content="Reply">
                      <CIconSvg size="xl">
                        <svg
                          fill="#000000"
                          height="800px"
                          width="800px"
                          version="1.1"
                          id="Layer_1"
                          xmlns="http://www.w3.org/2000/svg"
                          xmlns:xlink="http://www.w3.org/1999/xlink"
                          viewBox="0 0 512 512"
                          enable-background="new 0 0 512 512"
                          xml:space="preserve"
                        >
                          <path d="M185.2,128.6V19.7L0,204.9l185.2,185.2V281.1c152.5,0,250.5,0,326.8,217.9C512,390.1,522.9,128.6,185.2,128.6z" />
                        </svg>
                      </CIconSvg>
                    </CTooltip>
                  </CCol>
                  <CCol className="text-center" onClick={() => handleSideMenu(2)}>
                    <CTooltip content="Notes">
                      <CIcon size="xl" icon={cilBook} />
                    </CTooltip>
                  </CCol>
                  <CCol className="text-center" onClick={() => handleSideMenu(3)}>
                    <CTooltip content="Tasks">
                      <CIcon size="xl" icon={cilClipboard} />
                    </CTooltip>
                  </CCol>
                  <CCol className="text-center" onClick={() => handleSideMenu(4)}>
                    <CTooltip content="Transfer">
                      <CIcon size="xl" icon={cilTransfer} />
                    </CTooltip>
                  </CCol>
                  <CCol className="text-center" onClick={() => handleSideMenu(5)}>
                    <CTooltip content="Accounts">
                      <CIcon size="xl" icon={cilBuilding} />
                    </CTooltip>
                  </CCol>
                  <CCol className="text-center" onClick={() => handleSideMenu(6)}>
                    <CTooltip content="Merge Tickets">
                      <CIcon size="xl" icon={cilWindowRestore} />
                    </CTooltip>
                  </CCol>
                </CRow>
              </CCardBody>
            </CCard>

            {!sideMenu && (
              <>
                <CCard>
                  <CCardHeader>
                    <CCardTitle>Ticket Activity</CCardTitle>
                  </CCardHeader>
                  <CCardBody>
                    <div>
                      {timelineData.map((item, index) => (
                        <CRow className="mb-3" key={index}>
                          {/* Timeline Icon */}
                          {/* <CCol xs="1" className="text-center align-content-center">
                        <CIcon icon={item.icon} size="lg" className={`text-${item.badgeColor}`} />
                      </CCol> */}
                          <CCol
                            xs="1"
                            className="position-relative text-center align-content-center"
                          >
                            {/* Icon */}
                            <CIcon
                              icon={item.icon}
                              size="lg"
                              className={`text-${item.badgeColor}`}
                            />

                            {/* Dotted line */}
                            {index < timelineData.length - 1 && ( // Don't add the line after the last item
                              <div className="timeline-line"></div>
                            )}
                          </CCol>

                          {/* Timeline Content */}
                          <CCol xs="11">
                            <CCard
                              className="border-light"
                              style={{ boxShadow: '0 0.3rem 0.35rem 2px rgba(8, 10, 12, 0.075)' }}
                            >
                              {/* className= "shadow-sm" */}
                              <CCardBody>
                                <CBadge color={item.badgeColor} className="mb-2">
                                  {item.title}
                                </CBadge>
                                <CCardText>{item.description}</CCardText>
                                <small className="text-muted">{item.date}</small>
                              </CCardBody>
                            </CCard>
                          </CCol>
                        </CRow>
                      ))}
                    </div>
                  </CCardBody>
                </CCard>
              </>
            )}
            {sideMenu == 1 && (
              <>
                <CCard>
                  <CCardHeader>
                    <CCardTitle>Reply</CCardTitle>
                  </CCardHeader>
                  <CCardBody>Reply On Ticket Details</CCardBody>
                </CCard>
              </>
            )}
            {sideMenu == 2 && (
              <>
                <CCard>
                  <CCardHeader>
                    <CCardTitle>Notes</CCardTitle>
                  </CCardHeader>
                  <CCardBody>
                    {/* <CFormLabel htmlFor="notes">
                    Notes
                  </CFormLabel> */}
                    <CFormTextarea
                      // type="textarea"
                      id="notes"
                      name="notes"
                      rows={3}
                      placeholder="Enter Notes Here..."
                      // value=""
                      // onChange={handleChange}
                      // onChange={(e) => setLiveSearch(e.target.value)}
                      // required
                    />
                  </CCardBody>
                  <CCardFooter className="d-grid gap-2 d-md-flex justify-content-md-end">
                    <CButton color="primary">Save</CButton>
                  </CCardFooter>
                </CCard>
              </>
            )}
            {sideMenu == 3 && (
              <>
                <CCard>
                  <CCardHeader>
                    <CCardTitle>Tasks</CCardTitle>
                  </CCardHeader>
                  <CCardBody>Any Tasks Assigned</CCardBody>
                </CCard>
              </>
            )}
            {sideMenu == 4 && (
              <>
                <CCard>
                  <CCardHeader>
                    <CCardTitle>Transfers</CCardTitle>
                  </CCardHeader>
                  <CCardBody>
                    <div className="d-grid gap-2">
                      <CButton color="primary">Transfer Ticket</CButton>
                    </div>
                    <hr />
                    <h5>Transferred Ticket Logs</h5>
                    Transfered To : &lt;Name of new assignee&gt;
                    <br />
                    Transferred Time : &lt;Time&gt;
                    <br />
                  </CCardBody>
                </CCard>
              </>
            )}
            {sideMenu == 5 && (
              <>
                <CCard>
                  <CCardHeader>
                    <CCardTitle>Account Details</CCardTitle>
                  </CCardHeader>
                  <CCardBody>All the Details of Account here</CCardBody>
                </CCard>
              </>
            )}
            {sideMenu == 6 && (
              <>
                <CCard>
                  <CCardHeader>
                    <CCardTitle>Merge Tickets</CCardTitle>
                  </CCardHeader>
                  <CCardBody>
                    <div className="d-grid gap-2">
                      <CButton color="primary">Merge Ticket</CButton>
                    </div>
                    <hr />
                    <h5>Merged Ticket Logs</h5>
                    Merged with: &lt;Ticket Number&gt;
                    <br />
                    Merged Time : &lt;Time&gt;
                    <br />
                    Merge Note : &lt;Note&gt;
                    <br />
                  </CCardBody>
                </CCard>
              </>
            )}
          </CCol>
        </CRow>
      </CContainer>
    </>
  )
}

export default ViewSupportTickets
