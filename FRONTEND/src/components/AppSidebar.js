import React from 'react'
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
import CIcon from '@coreui/icons-react'

import { AppSidebarNav } from './AppSidebarNav'
import { AppSidebarNavSuperAdmin } from './superAdmin/AppSidebarNavSuperAdmin'
import { AppSidebarNavAgent } from './agent/AppSidebarNavAgent'
import { AppSidebarNavNeoxDash } from './AppSidebarNavNeoxDash'

import { logo } from 'src/assets/brand/logo'
import { sygnet } from 'src/assets/brand/sygnet'

// sidebar nav config
import navigation from '../_nav'
import navigationDash from '../_navDash'

const AppSidebar = () => {
  const dispatch = useDispatch()
  const unfoldable = useSelector((state) => state.sidebarUnfoldable)
  const sidebarShow = useSelector((state) => state.sidebarShow)

  let height
  if (unfoldable === false || unfoldable === undefined) {
    height = 32
  } else {
    height = 16
  }

  const sidebarColor = {
    '--cui-sidebar-bg': "url('../../src/assets/brand/bg_img.png'), linear-gradient(to bottom, #1F222A, #1B47C5)",
  }

  return (
    <CSidebar style={sidebarColor}
      className="border-end"
      colorScheme="dark"
      position="fixed"
      unfoldable={unfoldable}
      visible={sidebarShow}
      onVisibleChange={(visible) => {
        dispatch({ type: 'set', sidebarShow: visible })
      }}
    >
      <CSidebarHeader className="border-bottom">
        <CSidebarBrand to="/" as={NavLink}>
          <CImage
            // customClassName="sidebar-brand-full"
            src="../src/assets/brand/Asset-1as.png"
            height={height}
          />
        </CSidebarBrand>
        <CCloseButton
          className="d-lg-none"
          dark
          onClick={() => dispatch({ type: 'set', sidebarShow: false })}
        />
      </CSidebarHeader>
      {/* {(window.location.pathname != '/agent') ? (
        <AppSidebarNav items={navigation} />
      ) : (
        <AppSidebarNavAgent items={navigation} />
      )
      } */}
      {
        (() => {
          let pathname = window.location.pathname;

          if (pathname.startsWith('/super_admin')) {
            return <AppSidebarNavSuperAdmin items={navigation} />;
          } else if (pathname.startsWith('/agent')) {
            return <AppSidebarNavAgent items={navigation} />;
          } else if (pathname.startsWith('/neox_dash')) {
            return <AppSidebarNavNeoxDash items={navigationDash} />;
          } else {
            return <AppSidebarNav items={navigation} />;
          }
        })()
      }

      <CSidebarFooter className="border-top d-none d-lg-flex">
        <CSidebarToggler
          onClick={() => dispatch({ type: 'set', sidebarUnfoldable: !unfoldable })}
        />
      </CSidebarFooter>
    </CSidebar>
  )
}

export default React.memo(AppSidebar)
