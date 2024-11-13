import React, { useContext } from 'react'
import { CFooter } from '@coreui/react'
import { AuthContext } from '../context/AuthContext'

const AppFooter = () => {
  const { brandingInfo } = useContext(AuthContext)

  return (
    <CFooter className="px-4">
      <div>
        <a href="https://stl.tech/solutions/neox/" target="_blank" rel="noopener noreferrer">
          STL NEOX
        </a>
        <span className="ms-1">&copy; 2024 STL NEOX</span>
      </div>
      <div className="ms-auto">
        <span className="me-1">Powered by</span>
        <a
          href={brandingInfo?.footer_url || 'https://coreui.io/react'}
          target="_blank"
          rel="noopener noreferrer"
        >
          {brandingInfo?.footer_content || 'CoreUI React Admin & Dashboard'}
        </a>
      </div>
    </CFooter>
  )
}

export default React.memo(AppFooter)
