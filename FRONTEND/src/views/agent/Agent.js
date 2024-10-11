import React from 'react'
import { AppContent, AppSidebar, AppFooter, AppHeader } from '../../components/index'
import AppContentAgent from '../../components/agent/AppContentAgent'
import VerticalButtonGroup from './VerticalButtonGroup'

const DefaultLayoutAgent = () => {
  return (
    <div>
      <AppSidebar />
      <div className="wrapper d-flex flex-column min-vh-100">
        <AppHeader />
        <div className="body flex-grow-1">
          <AppContentAgent />
        </div>
        <VerticalButtonGroup />
        <AppFooter />
      </div>
    </div>
  )
}

export default DefaultLayoutAgent
