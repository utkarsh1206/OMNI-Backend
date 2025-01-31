import React, { useContext, useEffect, useState } from 'react'
import {
  CAvatar,
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CCardTitle,
  CCol,
  CImage,
  CRow,
  CTable,
  CTableBody,
  CTableDataCell,
  CTableHead,
  CTableHeaderCell,
  CTableRow,
  CWidgetStatsA,
} from '@coreui/react'
import { Link } from 'react-router-dom'
import axios from 'axios'
import { BASE_URL } from '../../config'
import { AuthContext } from '../../context/AuthContext'
import { CChart, CChartBar } from '@coreui/react-chartjs'
import SimpleBar from 'simplebar-react'

const Agent = ({ user }) => {
  const [tenantDash, setTenantDash] = useState(null)
  const [error, setError] = useState(null)
  const { authToken } = useContext(AuthContext)

  useEffect(() => {
    const fetchTenantDashboard = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/api/dashboard/ticket_details`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        setTenantDash(response.data)
      } catch (err) {
        setError('Error fetching Tenant Dashboard Details')
      }
    }

    fetchTenantDashboard()
  }, [authToken])

  if (error) {
    return <div>Error: {error}</div>
  }

  if (!tenantDash) {
    return <div>Loading...</div>
  }

  const {
    total_users,
    total_accounts,
    total_pending_tickets,
    total_departments,
    total_contacts,
    total_ticket_count,
    tickets_by_status,
    tickets_by_priority,
    tickets_by_department,
    tickets_by_product,
    pending_tickets_by_agent,
    tickets_by_incident_type,
    tickets_by_type,
    activity_summary,
    deleted_ticket_count,
  } = tenantDash

  // Extract data for the chart
  const ticketStatusLabels = Object.keys(tenantDash.tickets_by_status)
  const ticketStatusCounts = Object.values(tenantDash.tickets_by_status).map(
    (status) => status.count,
  )

  const ticketPriorityLabels = Object.keys(tenantDash.tickets_by_priority)
  const ticketPriorityCounts = Object.values(tenantDash.tickets_by_priority).map(
    (priority) => priority.count,
  )

  const ticketTypeLabels = Object.keys(tenantDash.tickets_by_type)
  const ticketTypeCounts = Object.values(tenantDash.tickets_by_type).map((type) => type.count)

  // Parse the tickets_by_department data
  const departmentNames = Object.keys(tickets_by_department)
  const departmentCounts = departmentNames.map((key) => tickets_by_department[key].count)

  const cards = [
    // { label: 'Total Accounts', value: total_accounts, icon: '🏢', color: '#1E90FF' },
    // { label: 'Total Users', value: total_users, icon: '👥', color: '#FFD700' },
    { label: 'Total Tickets', value: total_ticket_count, icon: '📋', color: '#FF6347' },
    { label: 'Pending Ticket', value: total_pending_tickets, icon: '📊', color: '#00CED1' },
    { label: 'Total Departments', value: total_departments, icon: '🗃️', color: '#32CD32' },
    // { label: 'Total Contacts', value: total_contacts, icon: '📞', color: '#FFA500' },
    // { label: 'SLA Breached', value: 42, icon: '❌', color: '#8A2BE2' }, // Extra card example
  ]

  return (
    <>
      <CRow>
        <CCol className="mb-3">
          <h3>{user.username}'s Dashboard</h3>
        </CCol>
        <CCol className="text-end">
          <Link to={'/spprt_tckt/crt_tckt'}>
            <CButton color="warning">Add Ticket</CButton>
          </Link>
        </CCol>
      </CRow>

      <CRow className="mb-3">
        <CCol>
          <CCard style={{ overflowX: 'auto' }}>
            <CCardBody>
              <div
                style={{
                  display: 'flex',
                  flexWrap: 'nowrap',
                  gap: '3rem',
                  alignItems: 'center',
                  whiteSpace: 'nowrap',
                }}
              >
                {cards.map((card, index) => (
                  <div
                    key={index}
                    style={{
                      display: 'inline-flex',
                      flexDirection: 'row',
                      alignItems: 'center',
                      gap: '0.5rem',
                      minWidth: 'fit-content',
                    }}
                  >
                    <div
                      style={{
                        width: '25px',
                        height: '25px',
                        borderRadius: '50%',
                        backgroundColor: card.color,
                        display: 'flex',
                        justifyContent: 'center',
                        alignItems: 'center',
                        fontSize: '12px',
                        color: '#fff',
                      }}
                    >
                      {card.icon}
                    </div>
                    <div style={{ textAlign: 'left' }}>
                      <div style={{ fontWeight: 'bold', fontSize: '14px' }}>{card.value}</div>
                      <div
                        style={{
                          fontSize: '12px',
                          color: '#6c757d',
                          overflow: 'hidden',
                          textOverflow: 'ellipsis',
                        }}
                        title={card.label}
                      >
                        {card.label}
                      </div>
                    </div>
                  </div>
                ))}
              </div>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      <CRow className="mb-3">
        <CCol xs="12" md="6">
          <CCard>
            <CCardHeader>
              <h4>Tickets by Department</h4>
            </CCardHeader>
            <CCardBody>
              <CChartBar
                data={{
                  labels: departmentNames, // Department names as labels
                  datasets: [
                    {
                      label: 'Number of Tickets',
                      backgroundColor: '#339af0', // Customize bar color
                      borderColor: '#1c7ed6',
                      borderWidth: 2,
                      borderRadius: 5,
                      hoverBackgroundColor: '#1c7ed6',
                      hoverBorderColor: '#1864ab',
                      data: departmentCounts, // Ticket counts as data
                    },
                  ],
                }}
                options={{
                  responsive: true,
                  maintainAspectRatio: false,
                  plugins: {
                    legend: {
                      display: false,
                      position: 'bottom',
                    },
                  },
                  scales: {
                    x: {
                      grid: {
                        display: false,
                      },
                    },
                    y: {
                      beginAtZero: true,
                      ticks: {
                        stepSize: 1,
                      },
                    },
                  },
                }}
                style={{ height: '170px', width: '100%' }}
              />
            </CCardBody>
          </CCard>
        </CCol>
        <CCol xs="12" md="6">
          <CRow>
            <h4>Activity Summary:</h4>
          </CRow>
          <CRow>
            <CCol xs="4">
              <CWidgetStatsA
                color="warning"
                value={<h2>{activity_summary.today}</h2>}
                title="Today"
              />
            </CCol>
            <CCol xs="4">
              <CWidgetStatsA
                color="primary"
                value={<h2>{activity_summary.last_7_days}</h2>}
                title="Last 7 Days"
              />
            </CCol>
            <CCol xs="4">
              <CWidgetStatsA
                color="info"
                value={<h2>{activity_summary.last_30_days}</h2>}
                title="Last 30 Days"
              />
            </CCol>
          </CRow>
          <CRow className="mt-2">
            <h4>Tickets by Product:</h4>
          </CRow>
          <div style={{ overflowX: 'auto', whiteSpace: 'nowrap', paddingBottom: '10px' }}>
            <SimpleBar>
              <CRow style={{ display: 'flex', flexWrap: 'nowrap', gap: '2px' }} className="mb-3">
                {tickets_by_product.map((product, index) => (
                  <div
                    key={index}
                    style={{ flex: '0 0 auto', minWidth: '100px', maxWidth: '200px' }}
                  >
                    <CWidgetStatsA
                      color="info"
                      value={<h2>{product.count}</h2>}
                      title={`Product ${product.product_id}`}
                    />
                  </div>
                ))}
              </CRow>
            </SimpleBar>
          </div>
        </CCol>
      </CRow>

      <CRow className="mb-4">
        {tickets_by_status && (
          <>
            <CCol xs="2">
              <CWidgetStatsA
                color="warning"
                value={<h2>{tickets_by_status.Unassigned?.count || 0}</h2>}
                title="Unassigned"
              />
            </CCol>
            <CCol xs="2">
              <CWidgetStatsA
                color="primary"
                value={<h2>{tickets_by_status.Open?.count || 0}</h2>}
                title="Open"
              />
            </CCol>
            <CCol xs="2">
              <CWidgetStatsA
                color="info"
                value={<h2>{tickets_by_status['Waiting on Customer']?.count || 0}</h2>}
                title="Waiting On Customer"
              />
            </CCol>
            <CCol xs="2">
              <CWidgetStatsA
                color="success"
                value={<h2>{tickets_by_status['Waiting on Third Party']?.count || 0}</h2>}
                title="Waiting On Third Party"
              />
            </CCol>
            <CCol xs="2">
              <CWidgetStatsA
                color="secondary"
                value={<h2>{tickets_by_status.Resolved?.count || 0}</h2>}
                title="Resolved"
              />
            </CCol>
            <CCol xs="2">
              <CWidgetStatsA
                color="dark"
                value={<h2>{tickets_by_status.Closed?.count || 0}</h2>}
                title="Closed"
              />
            </CCol>
          </>
        )}
      </CRow>

      <CRow>
        {/* Ticket Status */}
        <CCol lg="4" className="mb-4">
          <CCard>
            <CCardHeader>
              <CCardTitle>Ticket Status</CCardTitle>
            </CCardHeader>
            <CCardBody>
              <CChart
                type="pie"
                data={{
                  // labels: ['Unassigned', 'Pending', 'Deleted', 'Suspended', 'Solved'],
                  labels: ticketStatusLabels,
                  datasets: [
                    {
                      // data: [2215, 254, 587, 436, 0],
                      data: ticketStatusCounts,
                      backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
                        '#4CAF50',
                      ],
                    },
                  ],
                }}
                // options={{
                //   responsive: true,
                //   maintainAspectRatio: false,
                // }}
              />
            </CCardBody>
          </CCard>
        </CCol>
        {/* Ticket Priority */}
        <CCol lg="4" className="mb-4">
          <CCard>
            <CCardHeader>
              <CCardTitle>Ticket priority</CCardTitle>
            </CCardHeader>
            <CCardBody>
              <CChart
                type="pie"
                data={{
                  labels: ticketPriorityLabels,
                  datasets: [
                    {
                      data: ticketPriorityCounts,
                      backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
                    },
                  ],
                }}
              />
            </CCardBody>
          </CCard>
        </CCol>
        {/* Ticket Type */}
        <CCol lg="4" className="mb-4">
          <CCard>
            <CCardHeader>
              <CCardTitle>Ticket Type</CCardTitle>
            </CCardHeader>
            <CCardBody>
              <CChart
                type="pie"
                data={{
                  labels: ticketTypeLabels,
                  datasets: [
                    {
                      data: ticketTypeCounts,
                      backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'],
                    },
                  ],
                }}
                // options={{
                //   responsive: true,
                //   maintainAspectRatio: false,
                // }}
              />
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      <CRow className="mb-4">
        <CCol xs="4">
          <CCard>
            <CCardBody>
              <h5>Tickets by Incident Type</h5>
              <CTable align="middle" hover responsive>
                <CTableHead>
                  <CTableRow>
                    <CTableHeaderCell>Incident Type</CTableHeaderCell>
                    <CTableHeaderCell>Count</CTableHeaderCell>
                  </CTableRow>
                </CTableHead>
                <CTableBody>
                  {tenantDash.tickets_by_incident_type.map((incident) => (
                    <CTableRow key={incident.incident_id}>
                      <CTableDataCell>{incident.incident_name}</CTableDataCell>
                      <CTableDataCell>{incident.count}</CTableDataCell>
                    </CTableRow>
                  ))}
                </CTableBody>
              </CTable>
            </CCardBody>
          </CCard>
        </CCol>
        <CCol xs="4">
          <CCard>
            <CCardBody>
              <h5>Priority Wise Tickets</h5>
              {/* <CTable align="middle" className="mb-0 border" hover responsive> */}
              <CTable align="middle" hover responsive>
                {/* <CTableHead color="light"> */}
                <CTableHead>
                  <CTableRow>
                    <CTableHeaderCell>Priority</CTableHeaderCell>
                    <CTableHeaderCell>Count</CTableHeaderCell>
                  </CTableRow>
                </CTableHead>
                <CTableBody>
                  {Object.entries(tickets_by_priority || {}).map(([priority, data]) => (
                    <CTableRow key={priority}>
                      <CTableDataCell>{priority}</CTableDataCell>
                      <CTableDataCell>{data.count}</CTableDataCell>
                    </CTableRow>
                  ))}
                </CTableBody>
              </CTable>
            </CCardBody>
          </CCard>
        </CCol>

        <CCol xs="4">
          <CCard>
            <CCardBody>
              <h5>Type Wise Tickets</h5>
              <CTable align="middle" hover responsive>
                <CTableHead>
                  <CTableRow>
                    <CTableHeaderCell>Type</CTableHeaderCell>
                    <CTableHeaderCell>Count</CTableHeaderCell>
                  </CTableRow>
                </CTableHead>
                <CTableBody>
                  {Object.entries(tickets_by_type || {}).map(([type, data]) => (
                    <CTableRow key={type}>
                      <CTableDataCell>{type}</CTableDataCell>
                      <CTableDataCell>{data.count}</CTableDataCell>
                    </CTableRow>
                  ))}
                </CTableBody>
              </CTable>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      <CRow>
        <CCol xs="4">
          <CCard className="mb-3">
            <CCardHeader>
              <CCardTitle>Pending Tickets</CCardTitle>
            </CCardHeader>
            <CCardBody>
              <CTable align="middle" className="mb-0 border" hover responsive>
                <CTableBody>
                  {tenantDash?.pending_tickets_by_agent.map((agent, index) => (
                    <CTableRow key={index}>
                      {/* Avatar */}
                      <CTableDataCell className="text-center" width={'10%'}>
                        <CAvatar size="md" color="primary" textColor="white">
                          {agent.agent_name.slice(0, 1)} {/* First letter of agent's name */}
                        </CAvatar>
                      </CTableDataCell>

                      {/* Agent Details */}
                      <CTableDataCell>
                        <div>{agent.agent_name}</div> {/* Agent Name */}
                        <div className="small text-body-secondary text-nowrap">
                          Agent ID - {agent.assign_to_id} {/* Agent ID */}
                        </div>
                      </CTableDataCell>

                      {/* Pending Tickets */}
                      <CTableDataCell width={'20%'}>
                        <div className="fw-semibold text-nowrap">{agent.count}</div>{' '}
                      </CTableDataCell>
                    </CTableRow>
                  ))}
                </CTableBody>
              </CTable>
            </CCardBody>
          </CCard>
        </CCol>

        {/* <CRow className="mt-2">
            <h4>Tickets by Department:</h4>
          </CRow>
          <CRow style={{ overflowX: 'auto' }}>
            {Object.entries(tickets_by_department).map(([key, department]) => (
              <CCol key={key}>
                <CWidgetStatsA
                  color="info"
                  value={<h2>{department.count}</h2>}
                  title={department.departments}
                />
              </CCol>
            ))}
          </CRow>
        </CCol> */}
      </CRow>
    </>
  )
}

export default Agent
