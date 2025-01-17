import React, { useContext, useEffect, useState } from 'react'
import {
  CContainer,
  CRow,
  CCol,
  COffcanvas,
  COffcanvasHeader,
  COffcanvasBody,
  CCloseButton,
} from '@coreui/react'
import axios from 'axios'
import { useParams } from 'react-router-dom'
import { AuthContext } from '../../context/AuthContext'
import TicketDetailsCard from './TicketDetailsCard'
import TicketDescription from './TicketDescription'
import TicketActivity from './TicketActivity'
import SideMenuActions from './SideMenuActions'
// import ReplyOffcanvas from '../components/ReplyOffcanvas'
import { BASE_URL } from '../../config'
import Notes from './Notes'
import Tasks from './Tasks'
import Transfers from './Transfers'
import AccountDetails from './AccountDetails'
import MergeTickets from './MergeTickets'
import Reply from './Reply'

const ViewSupportTickets = () => {
  const { id } = useParams()
  const { authToken, brandingInfo } = useContext(AuthContext)
  const [tickets, setTickets] = useState([])
  const [account, setAccount] = useState([])
  const [contact, setContact] = useState([])
  const [sideMenu, setSideMenu] = useState(null)

  // Fetch ticket data
  useEffect(() => {
    axios
      .get(`${BASE_URL}/api/supportticket/${id}`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authToken}`,
        },
      })
      .then((response) => {
        setTickets(response.data.data)
        document.title = `Support Ticket - ${response.data.data.id}`
      })
      .catch((error) => {
        console.error('Error fetching support ticket:', error)
        alert('Failed to fetch support ticket.')
      })

    // return () => {
    //   document.title = brandingInfo.title // Reset title
    // }
  }, [id])

  // useEffect(() => {
  //   if (tickets.account_id) {
  //     axios
  //       .get(`${BASE_URL}/api/accounts/${tickets.account_id}`, {
  //         headers: {
  //           'Content-Type': 'application/json',
  //           Authorization: `Bearer ${authToken}`,
  //         },
  //       })
  //       .then((response) => {
  //         setAccount(response.data.data)
  //       })
  //       .catch((error) => {
  //         console.error('Error fetching account details:', error)
  //         alert('Failed to fetch Account details')
  //       })
  //   }
  // }, [tickets.account_id])

  useEffect(() => {
    if (tickets.contact_id) {
      axios
        .get(`${BASE_URL}/api/contacts/${tickets.contact_id}`, {
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${authToken}`,
          },
        })
        .then((response) => {
          setContact(response.data.data)
        })
        .catch((error) => {
          console.error('Error fetching account details:', error)
          alert('Failed to fetch Account details')
        })
    }
  }, [tickets.contact_id])

  useEffect(() => {
    const fetchAccountDetails = async () => {
      if (tickets.account_id) {
        try {
          const response = await axios.get(`${BASE_URL}/api/accounts/${tickets.account_id}`, {
            headers: {
              'Content-Type': 'application/json',
              Authorization: `Bearer ${authToken}`,
            },
          })
          setAccount(response.data.data)
        } catch (error) {
          console.error('Error fetching account details:', error)
          alert('Failed to fetch Account details')
        }
      }
    }

    fetchAccountDetails()
  }, [tickets.account_id])

  const handleSideMenu = (id) => {
    setSideMenu((prev) => (prev === id ? null : id))
  }

  const handleOffcanvasClose = () => {
    setSideMenu(null)
  }

  return (
    <CContainer>
      <CRow>
        {/* Left Column */}
        <CCol md={8}>
          {console.log('contact details' + contact)}
          <TicketDetailsCard ticket={tickets} account={account} contact={contact} />
          <TicketDescription ticket={tickets} />
        </CCol>

        {/* Right Column */}
        <CCol className="mb-3">
          <SideMenuActions sideMenu={sideMenu} onToggle={handleSideMenu} />

          {/* Ticket Activity */}
          {!sideMenu && <TicketActivity ticket={tickets} />}

          {/* Reply */}
          {sideMenu === 1 && <Reply onClose={handleOffcanvasClose} ticket={tickets} />}

          {/* Notes */}
          {sideMenu === 2 && <Notes ticket={tickets} />}

          {/* Tasks */}
          {sideMenu === 3 && <Tasks />}

          {/* Transfers */}
          {sideMenu === 4 && <Transfers ticket={tickets} />}

          {/* Account Details */}
          {sideMenu === 5 && (
            <AccountDetails onClose={handleOffcanvasClose} accountId={tickets.account_id} />
          )}

          {/* Merge Tickets */}
          {sideMenu === 6 && <MergeTickets ticket={tickets} />}
        </CCol>
      </CRow>
    </CContainer>
  )
}

export default ViewSupportTickets
