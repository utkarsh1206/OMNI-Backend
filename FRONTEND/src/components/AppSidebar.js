import React, { useContext } from 'react'
import { NavLink } from 'react-router-dom'
import { useSelector, useDispatch } from 'react-redux'
import {
  CCloseButton,
  CImage,
  CSidebar,
  CSidebarBrand,
  CSidebarFooter,
  CSidebarHeader,
  CSidebarToggler,
} from '@coreui/react'

import { AppSidebarNav } from './AppSidebarNav'
import { AppSidebarNavSuperAdmin } from './superAdmin/AppSidebarNavSuperAdmin'
import { AppSidebarNavAgent } from './agent/AppSidebarNavAgent'
import { AppSidebarNavNeoxDash } from './AppSidebarNavNeoxDash'

import navigation from '../_nav'
import navigationDash from '../_navDash'
import { AuthContext } from '../context/AuthContext'
import navigationAgent from '../_navAgent'

const AppSidebar = () => {
  const dispatch = useDispatch()
  const unfoldable = useSelector((state) => state.sidebarUnfoldable)
  const sidebarShow = useSelector((state) => state.sidebarShow)

  const { user, brandingInfo } = useContext(AuthContext)

  const sidebarColor = {
    '--cui-sidebar-bg': "url('../../src/assets/brand/new_bg.png'), #fff",
  }

  return (
    <CSidebar
      style={sidebarColor}
      className="border-end"
      colorScheme="light"
      position="fixed"
      unfoldable={unfoldable}
      visible={sidebarShow}
      onVisibleChange={(visible) => dispatch({ type: 'set', sidebarShow: visible })}
    >
      <CSidebarHeader className="border-bottom">
        <CSidebarBrand to="/" as={NavLink}>
          <CImage
            src={brandingInfo?.tenant_logo || '../src/assets/brand/new_menu_logo.png'}
            height={unfoldable ? 16 : 32}
          />
        </CSidebarBrand>
        <CCloseButton
          className="d-lg-none"
          dark
          onClick={() => dispatch({ type: 'set', sidebarShow: false })}
        />
      </CSidebarHeader>
      {(() => {
        let pathname = window.location.pathname
        if (pathname.startsWith('/super_admin')) {
          return <AppSidebarNavSuperAdmin items={navigation} />
        } else if (pathname.startsWith('/agent')) {
          return <AppSidebarNavAgent items={navigationAgent} />
        } else if (pathname.startsWith('/neox_dash')) {
          return <AppSidebarNavNeoxDash items={navigationDash} />
        } else {
          return <AppSidebarNav items={navigation} />
        }
      })()}
      <CSidebarFooter className="border-top d-none d-lg-flex">
        <CSidebarToggler
          onClick={() => dispatch({ type: 'set', sidebarUnfoldable: !unfoldable })}
        />
      </CSidebarFooter>
    </CSidebar>
  )
}

export default React.memo(AppSidebar)

//       {/* {(window.location.pathname != '/agent') ? (
//         <AppSidebarNav items={navigation} />
//       ) : (
//         <AppSidebarNavAgent items={navigation} />
//       )
//       } */}
//       {(() => {
//         let pathname = window.location.pathname

//         if (pathname.startsWith('/super_admin')) {
//           return <AppSidebarNavSuperAdmin items={navigation} />
//         } else if (pathname.startsWith('/agent')) {
//           return <AppSidebarNavAgent items={navigation} />
//         } else if (pathname.startsWith('/neox_dash')) {
//           return <AppSidebarNavNeoxDash items={navigationDash} />
//         } else {
//           return <AppSidebarNav items={navigation} />
//         }
//       })()}
