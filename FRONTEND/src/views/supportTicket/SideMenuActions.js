import React from 'react'
import { CCard, CCardBody, CCol, CTooltip } from '@coreui/react'
import CIcon, { CIconSvg } from '@coreui/icons-react'
import { cilBook, cilClipboard, cilBuilding, cilTransfer, cilWindowRestore } from '@coreui/icons'

const SideMenuActions = ({ sideMenu, onToggle }) => {
  const menuItems = [
    { id: 1, tooltip: 'Reply' },
    { id: 2, tooltip: 'Notes', icon: cilBook },
    { id: 3, tooltip: 'Tasks', icon: cilClipboard },
    { id: 4, tooltip: 'Transfer', icon: cilTransfer },
    { id: 5, tooltip: 'Accounts', icon: cilBuilding },
    { id: 6, tooltip: 'Merge', icon: cilWindowRestore },
  ]

  return (
    <CCard className="mb-3">
      <CCardBody>
        <div className="d-flex justify-content-around">
          {menuItems.map((item) => (
            <CCol key={item.id} className="text-center" onClick={() => onToggle(item.id)}>
              <CTooltip content={item.tooltip}>
                {item.id === 1 ? (
                  <CIconSvg size="xl">
                    <svg
                      fill="#000000"
                      height="800px"
                      width="800px"
                      version="1.1"
                      id="Layer_1"
                      xmlns="http://www.w3.org/2000/svg"
                      xmlns:xlink="http://www.w3.org/1999/xlink"
                      viewBox="0 0 512 512"
                      enable-background="new 0 0 512 512"
                      xml:space="preserve"
                    >
                      <path d="M185.2,128.6V19.7L0,204.9l185.2,185.2V281.1c152.5,0,250.5,0,326.8,217.9C512,390.1,522.9,128.6,185.2,128.6z" />
                    </svg>
                  </CIconSvg>
                ) : (
                  <CIcon size="xl" icon={item.icon} />
                )}
              </CTooltip>
            </CCol>
          ))}
        </div>
      </CCardBody>
    </CCard>
  )
}

export default SideMenuActions
