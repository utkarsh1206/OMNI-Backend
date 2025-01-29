import React, { useContext } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { AuthContext } from '../../context/AuthContext'
import SuperAdmin from './SuperAdmin'
import TenantAdmin from './TenantAdmin'
import Agent from './Agent'

const Dashboard = () => {
  const { user } = useContext(AuthContext)
  return (
    <div className="dashboard">
      {(() => {
        if (user.role_id == 0) {
          return <SuperAdmin />
        } else if (user.role_id == 1) {
          return <TenantAdmin user={user} />
        } else if (user.role_id == 2) {
          return <Agent user={user} />
        }
      })()}
    </div>
  )
}

export default Dashboard
