import React from 'react';
import AgentDashboard from './AgentDashboard';
import LiveCallsDashboard from './LiveCallsDashboard';


const Dashboard = () => {

  return (
    <>
      <LiveCallsDashboard />
      <br />
      <AgentDashboard />
    </>
  );
}

export default Dashboard;