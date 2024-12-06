import React from 'react'
import { CCard, CCardBody, CCardHeader, CCardTitle, CRow, CCol, CBadge, CCardText } from '@coreui/react'
import CIcon from '@coreui/icons-react'
import * as icon from '@coreui/icons'

const TicketActivity = () => {
    const timelineData = [
        {
          icon: 'cilUser',
          badgeColor: 'primary',
          title: 'Ticket Created',
          description: 'Santi contacted for the first time',
          date: 'Tue, 13 Dec 2023',
        },
        {
          icon: 'cilFire',
          badgeColor: 'danger',
          title: 'Type Changed',
          description: 'Changed Ticket Type to Incident',
          date: 'Tue, 13 Dec 2023',
        },
        {
          icon: 'cilPhone',
          badgeColor: 'info',
          title: 'Call Scheduled',
          description: 'Going on Call with +1 678-908-456',
          date: 'Tue, 13 Dec 2023',
        },
      ]

  return (
    <CCard>
      <CCardHeader>
        <CCardTitle>Ticket Activity</CCardTitle>
      </CCardHeader>
      <CCardBody>
        <div>
          {timelineData.map((item, index) => (
            <CRow className="mb-3" key={index}>
              <CCol xs="1" className="position-relative text-center align-content-center">
                {/* Icon */}
                <CIcon icon={icon[item.icon]} size="lg" className={`text-${item.badgeColor}`} />
                {/* Dotted line */}
                {index < timelineData.length - 1 && <div className="timeline-line"></div>}
              </CCol>
              <CCol xs="11">
                <CCard className="border-light" style={{ boxShadow: '0 0.3rem 0.35rem 2px rgba(8, 10, 12, 0.075)' }}>
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
  )
}

export default TicketActivity
