import React, { Suspense } from 'react'
import { Navigate, Route, Routes } from 'react-router-dom'
import { CContainer, CSpinner } from '@coreui/react'
import getUserPermissions from '../utils/userPermissionsUtils'
import Page404 from '../views/pages/page404/Page404'

// routes config
import routes from '../routes'

const AppContent = () => {
  const authToken = localStorage.getItem('authToken');
  const userPermissions = getUserPermissions();

  return (
    <CContainer className="px-3" lg>
      <Suspense fallback={<CSpinner color="primary" />}>
        <Routes>
          {routes.map((route, idx) => {
            return (
              route.element &&
              (route.permissionTag === true || userPermissions.includes(route.permissionTag)) &&
              (
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
          <Route
            path='*'
            element={<Page404 redirectToPage={true} />}
          />
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
