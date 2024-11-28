import React, { Suspense } from 'react'
import { Navigate, Route, Routes } from 'react-router-dom'
import { CContainer, CSpinner } from '@coreui/react'

// routes config
import routes from '../routes'

const AppContent = () => {
  const authToken = localStorage.getItem('authToken');

  return (
    <CContainer className="px-3" lg>
      <Suspense fallback={<CSpinner color="primary" />}>
        <Routes>
          {routes.map((route, idx) => {
            return (
              route.element && (
                <Route
                  key={idx}
                  path={route.path}
                  exact={route.exact}
                  name={route.name}
                  element={<route.element />}
                />
              )
            )
          })}
          {/* <Route path="/" element={<Navigate to="dashboard" replace />} /> */}
          {/* <Route path="/" element={<Navigate to="user" replace />} /> */}
          {authToken ? (
            <Route path="/" element={<Navigate to="user" replace />} />
          ) : (
            <Route path="/" element={<Navigate to="login" replace />} />
          )}
          {/* <Route path="/" element={<Navigate to="user" replace />} /> */}
        </Routes>
      </Suspense>
    </CContainer>
  )
}

export default React.memo(AppContent)
