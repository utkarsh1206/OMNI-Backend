import React from 'react'
import { CFooter } from '@coreui/react'

const AppFooter = () => {
  return (
    <CFooter className="px-4">
      <div>
        <a href="https://stl.tech/solutions/neox/" target="_blank" rel="noopener noreferrer">
          STL Neox
        </a>
        <span className="ms-1">&copy; 2024 STL Neox</span>
      </div>
      <div className="ms-auto">
        <span className="me-1">Powered by</span>
        <a href="https://coreui.io/react" target="_blank" rel="noopener noreferrer">
          CoreUI React Admin &amp; Dashboard
        </a>
      </div>
    </CFooter>
  )
}

export default React.memo(AppFooter)
