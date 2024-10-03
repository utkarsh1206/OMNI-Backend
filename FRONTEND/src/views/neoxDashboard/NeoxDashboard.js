import React from 'react'
import { AppContent, AppSidebar, AppFooter, AppHeader } from '../../components/index'
// import AppContentAgent from '../../components/AppContentAgent'
import AppContentNeoxDashboard from '../../components/AppContentNeoxDashboard'
// import VerticalButtonGroup from './VerticalButtonGroup'

const DefaultLayoutNeoxDashboard = () => {
  return (
    <div>
      <AppSidebar />
      <div className="wrapper d-flex flex-column min-vh-100">
        <AppHeader />
        <div className="body flex-grow-1">
          <AppContentNeoxDashboard />
        </div>
        {/* <VerticalButtonGroup /> */}
        <AppFooter />
      </div>
    </div>
  )
}

export default DefaultLayoutNeoxDashboard
