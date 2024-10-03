import React, { Suspense } from 'react'
import { Navigate, Route, Routes } from 'react-router-dom'
import { CContainer, CSpinner } from '@coreui/react'

// routes config
import routes from '../routes'
import NeoxDashboardBody from '../views/neoxDashboard/NeoxDashboardBody'

const AppContent = () => {
  return (
    <CContainer className="px-4" fluid>
      <Suspense fallback={<CSpinner color="primary" />}>

        <NeoxDashboardBody />

      </Suspense>
    </CContainer >
  )
}

export default React.memo(AppContent)
