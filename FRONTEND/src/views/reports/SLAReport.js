import React, { useState, useEffect, useContext } from 'react'
import {
  CTable,
  CTableHead,
  CTableRow,
  CTableHeaderCell,
  CTableBody,
  CTableDataCell,
  CRow,
  CCol,
  CCard,
  CCardHeader,
  CCardBody,
  CButton,
  CFormSelect,
  CContainer
} from "@coreui/react";
import Datetime from 'react-datetime'
import 'react-datetime/css/react-datetime.css'
import axios from 'axios';
import { BASE_URL } from '../../config';
import { AuthContext } from '../../context/AuthContext'
import moment from 'moment'
import PaginationForReport from '../../components/PaginationForReport';
import { CChart } from '@coreui/react-chartjs'

const SLAReport = () => {
  const { authToken, user } = useContext(AuthContext)
  const [startDate, setStartDate] = useState(moment().startOf('day')) // Default to today 00:00:00
  const [endDate, setEndDate] = useState(moment().endOf('day')) // Default to today 23:59:59
  const [showGraph, setShowGraph] = useState(true);
  const [currentPage, setCurrentPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [total, setTotal] = useState(0)
  const [perPage, setPerPage] = useState(10);

  const [slaData, setSlaData] = useState([]);
  const [graphData, setGraphData] = useState({
    priority: null,
    category: null,
    incident: null
  });
  const [slaComplianceCount, setSlaComplianceCount] = useState({ compliant: 0, nonCompliant: 0 });
  const [userFilter, setUserFilter] = useState("all");
  const [users, setUsers] = useState([]);
  const [priorityFilter, setPriorityFilter] = useState('all');
  const [slaComplianceFilter, setSlaComplianceFilter] = useState("all");
  const [chartData, setChartData] = useState({
    users: [],
    avgActualTimes: [],
    avgExpectedTimes: [],
  });

  const isUser = user.role_id == 3

  const fetchUsers = async () => {
    try {
      const response = await axios.get(`${BASE_URL}/api/users`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
        },
      });
      setUsers(response.data.data);
    } catch (error) {
      console.error('Error fetching users:', error);
    }
  };

  const handleSearch = async (pageNumber = currentPage, perPageValue = perPage) => {
    const startDateStr = startDate.format('YYYY-MM-DD HH:mm:ss');
    const endDateStr = endDate.format('YYYY-MM-DD HH:mm:ss');
    const userFilterStr = userFilter ? userFilter : 'all';

    try {
      const response = await axios.get(`${BASE_URL}/api/slareport`, {
        params: {
          startDate: startDateStr,
          endDate: endDateStr,
          page: pageNumber,
          perPage: perPageValue,
          user: userFilterStr === 'all' ? undefined : userFilterStr,
          priority: priorityFilter === 'all' ? undefined : priorityFilter,
          slaCompliance: slaComplianceFilter === 'all' ? undefined : slaComplianceFilter,
        },
        headers: {
          Authorization: `Bearer ${authToken}`,
        },
      });
      setSlaData(response.data.data);
      setCurrentPage(response.data.meta.current_page)
      setLastPage(response.data.meta.last_page)
      setTotal(response.data.meta.total)
      setPerPage(response.data.meta.perPage)

      // Call the calculation method with the full dataset
      const allDataResponse = await axios.get(`${BASE_URL}/api/slareport?page=all`, {
        params: {
          startDate: startDateStr,
          endDate: endDateStr,
          user: userFilterStr === 'all' ? undefined : userFilterStr,
          priority: priorityFilter === 'all' ? undefined : priorityFilter,
          slaCompliance: slaComplianceFilter === 'all' ? undefined : slaComplianceFilter,
        },
        headers: {
          Authorization: `Bearer ${authToken}`,
        },
      });
      console.log("all data ", allDataResponse.data.data)
      calculateSlaCompliance(allDataResponse.data.data);
      calculateResolutionTimes(allDataResponse.data.data);

      fetchGraphData(startDateStr, endDateStr, userFilterStr, priorityFilter, slaComplianceFilter);
    } catch (error) {
      console.error('Error fetching tickets:', error);
    }
  }


  const handleCSVDownload = async (pageNumber = currentPage, perPageValue = perPage) => {
    try {
      const startDateStr = startDate.format('YYYY-MM-DD HH:mm:ss');
      const endDateStr = endDate.format('YYYY-MM-DD HH:mm:ss');
      const userFilterStr = userFilter ? userFilter : 'all';

      const response = await axios.get(`${BASE_URL}/api/slareport/csv`, {
        params: {
          startDate: startDateStr,
          endDate: endDateStr,
          user: userFilterStr === 'all' ? undefined : userFilterStr,
          priority: priorityFilter === 'all' ? undefined : priorityFilter,
          slaCompliance: slaComplianceFilter === 'all' ? undefined : slaComplianceFilter,
          page: pageNumber,
          perPage: perPageValue,
        },
        responseType: 'blob',
        headers: {
          Authorization: `Bearer ${authToken}`,
        },
      });

      const blob = new Blob([response.data], { type: 'text/csv' });
      const downloadUrl = window.URL.createObjectURL(blob);
      const now = moment().format('YYYYMMDD_HHmmss');
      const fileName = `SLA_ticket_report_csv_${now}.csv`;

      const link = document.createElement('a');
      link.href = downloadUrl;
      link.setAttribute('download', fileName);
      document.body.appendChild(link);
      link.click();

      window.URL.revokeObjectURL(downloadUrl);
    } catch (error) {
      console.error('Error downloading CSV:', error);
    }
  };

  const calculateSlaCompliance = (data) => {
    console.log("data". data);
    const yes = data.filter(ticket => ticket.sla_compliance === 'Yes').length;
    const no = data.filter(ticket => ticket.sla_compliance === 'No').length;
    setSlaComplianceCount({ yes, no });
  };

  const calculateResolutionTimes = (data) => {
    try {
      const userMap = {};

      data.forEach(ticket => {
        const user = ticket.user_name;
        const actualResolvedTime = ticket.actual_resolved_time ? moment.duration(ticket.actual_resolved_time).asMinutes() : 0;
        const expectedResolveTime = ticket.resolve_time_value ? moment.duration(ticket.resolve_time_value).asMinutes() : 0;


        console.log("actualResolvedTime", ticket.actual_resolved_time);
        if (!userMap[user]) {
          userMap[user] = { totalActual: 0, totalExpected: 0, count: 0 };
        }
        userMap[user].totalActual += actualResolvedTime;
        userMap[user].totalExpected += expectedResolveTime;
        userMap[user].count++;
      });

      const users = Object.keys(userMap);
      const avgActualTimes = users.map(user => (userMap[user].totalActual / userMap[user].count).toFixed(2));
      const avgExpectedTimes = users.map(user => (userMap[user].totalExpected / userMap[user].count).toFixed(2));

      setChartData({
        users,
        avgActualTimes,
        avgExpectedTimes,
      });

    } catch (error) {
      console.error('Error fetching graph data:', error);
    }
  }

  const handlePageChange = (newPage) => {
    setCurrentPage(newPage);
    handleSearch(newPage, perPage); // Call handleSearch with new page and current perPage
  };

  const handlePerPageChange = (e) => {
    const newPerPage = Number(e.target.value);
    setPerPage(newPerPage);
    setCurrentPage(1); // Reset to the first page when perPage changes
    handleSearch(1, newPerPage); // Call handleSearch with first page and new perPage
  };

  const handlePerPageValue = (perPage) => {
    setPerPage(perPage)  // Update the current page when pagination changes
  }

  const handleGraphToggle = () => {
    setShowGraph(!showGraph);
  };

  const fetchGraphData = async (startDateStr, endDateStr, userFilterStr, priorityFilter, slaComplianceFilter) => {
    try {
      const params = {
        startDate: startDateStr,
        endDate: endDateStr,
        user: userFilterStr === 'all' ? undefined : userFilterStr,
        priority: priorityFilter === 'all' ? undefined : priorityFilter,
        slaCompliance: slaComplianceFilter === 'all' ? undefined : slaComplianceFilter,
      };

      const headers = { Authorization: `Bearer ${authToken}` };

      const [priorityRes, categoryRes, incidentRes] = await Promise.all([
        axios.get(`${BASE_URL}/api/sla-priority`, { headers, params }),
        axios.get(`${BASE_URL}/api/sla-category`, { headers, params }),
        axios.get(`${BASE_URL}/api/sla-incident`, { headers, params }),
      ]);

      setGraphData({
        priority: mapData(priorityRes.data.data, "priority"),
        category: mapData(categoryRes.data.data, "category"),
        incident: mapData(incidentRes.data.data, "incident_name"),
      });
    } catch (error) {
      console.error('Error fetching graph data:', error);
    }
  };

  const mapData = (data, labelKey) => {
    const labels = data.map((item) => item[labelKey]);
    const values = data.map((item) => item["ticket_count"]);
    return { labels, values };
  };

  useEffect(() => {
    fetchUsers();
    handleSearch();
  }, []);

  return (
    <>
      <CRow>
        <CCol xs={12}>
          <CCard className="mb-2">
            <CCardHeader>
              <div>
                Search for Filters
              </div>
            </CCardHeader>
            <CCardBody>
              <CRow className="mb-3">
                <CCol xs={2}>
                  <label>Start Date and Time</label>
                  <Datetime
                    value={startDate}
                    onChange={(date) => setStartDate(date)}
                    dateFormat="YYYY-MM-DD"
                    timeFormat="HH:mm:ss"
                  />
                </CCol>
                <CCol xs={2}>
                  <label>End Date and Time</label>
                  <Datetime
                    value={endDate}
                    onChange={(date) => setEndDate(date)}
                    dateFormat="YYYY-MM-DD"
                    timeFormat="HH:mm:ss"
                  />
                </CCol>
                {!isUser && (
                  <CCol xs={2}>
                    <label>User</label>
                    <CFormSelect
                      value={userFilter}
                      onChange={(e) => setUserFilter(e.target.value)}
                    >
                      <option value="all">Select User</option>
                      {users.map((user) => (
                        <option key={user.id} value={user.id}>{user.username}</option>
                      ))}
                    </CFormSelect>
                  </CCol>
                )}
                <CCol xs={2}>
                  <label>Priority</label>
                  <CFormSelect
                    value={priorityFilter}
                    onChange={(e) => setPriorityFilter(e.target.value)}
                  >
                    <option value="all">Select Priority</option>
                    <option value="Critical">Critical</option>
                    <option value="High">High</option>
                    <option value="Medium">Medium</option>
                    <option value="Low">Low</option>
                    <option value="Normal">Normal</option>
                  </CFormSelect>
                </CCol>
                <CCol xs={2}>
                  <label>SLA Compliance</label>
                  <CFormSelect
                    value={slaComplianceFilter}
                    onChange={(e) => setSlaComplianceFilter(e.target.value)}
                  >
                    <option value="all">Select Compliance</option>
                    <option value="Yes">Yes</option>
                    <option value="No">No</option>
                  </CFormSelect>
                </CCol>
              </CRow>
              <CButton color="primary"
                onClick={handleSearch}
              >
                Search
              </CButton>
            </CCardBody>
          </CCard>
        </CCol>
      </CRow>

      <hr />
      <CRow>
        <CCol>
          <CContainer>
            <CRow fluid responsive>
              <CCol md={8}>
                <CButton
                  color="primary"
                  className="mb-4 me-2"
                  size="sm"
                  onClick={handleGraphToggle}
                >
                  {showGraph ? 'Show Table View' : 'Show Graph View'}
                </CButton>

                <CButton
                  color="primary"
                  className="mb-4 me-2"
                  size="sm"
                  onClick={handleCSVDownload}
                >
                  CSV
                </CButton>
              </CCol>
            </CRow>
          </CContainer>
        </CCol>
      </CRow>

      {showGraph ? (
        <CRow>
          <CCol xs={12}>
            <CCard className="mb-2">
              <CContainer>
                <CRow className="mb-4" style={{ margin: '10px' }}>
                  <CCol>
                    <h5>Priority Summary</h5>
                    {graphData.priority && (
                      <div style={{ width: '400px' }}>
                        <CChart
                          type="pie"
                          data={{
                            labels: graphData.priority.labels,
                            datasets: [
                              {
                                label: 'SLA by Priority',
                                data: graphData.priority.values,
                                backgroundColor: [
                                  '#48A1B7',
                                  '#368197',
                                  '#9966FF',
                                  '#FF9F40',
                                  '#66BB6A'
                                ],
                                borderColor: 'rgba(255, 255, 255, 1)',
                                borderWidth: 2,
                              },
                            ],
                          }}
                          options={{
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                              legend: { display: true, position: 'top' },
                              tooltip: { enabled: true },
                            },
                          }}
                          style={{ height: '300px' }}
                        />
                      </div>
                    )}
                  </CCol>
                  <CCol>
                    <h5>SLA Compliance</h5>
                    {slaComplianceCount && (
                      <div style={{ width: '600px' }}>
                        <CChart
                          type="bar"
                          data={{
                            labels: ["Yes", "No"],
                            datasets: [
                              {
                                label: "SLA Compliance",
                                data: [slaComplianceCount.yes, slaComplianceCount.no],
                                backgroundColor: ["#7a49a5", "#4974a5"],
                                borderColor: "rgba(255, 255, 255, 1)",
                                borderWidth: 2,
                                barThickness: 30,
                              },
                            ],
                          }}
                          options={{
                            responsive: true,
                            indexAxis: 'y',
                            plugins: {
                              legend: { display: false },
                              tooltip: { enabled: true },
                            },
                            scales: {
                              x: {
                                beginAtZero: true,
                                grid: {
                                  display: false,
                                },
                              },
                              y: {
                                grid: {
                                  display: false,
                                },
                              },
                            },
                          }}
                          style={{ height: '200px' }}
                        />
                      </div>
                    )}
                  </CCol>
                </CRow>

                <CRow className="mb-4" style={{ margin: '10px' }}>
                  <h5>Category SLA Summary</h5>
                  {graphData.category && (
                    <div style={{ width: "900px" }}>
                      <CChart
                        type="bar"
                        data={{
                          labels: graphData.category.labels,
                          datasets: [
                            {
                              label: "Tickets by Category",
                              data: graphData.category.values,
                              backgroundColor: [
                                "#7a49a5",
                              ],
                              borderColor: [
                                "#BCA4D2 ",
                              ],
                              borderWidth: {
                                top: 15,
                                right: 0,
                                bottom: 0,
                                left: 15, // Border only on the left side
                              },
                              barThickness: 50,
                            },
                          ],
                        }}
                        options={{
                          responsive: true,
                          maintainAspectRatio: false,
                          plugins: {
                            legend: { display: true },
                            tooltip: { enabled: true },
                          },
                          scales: {
                            x: {
                              grid: { display: false },
                            },
                            y: {
                              beginAtZero: true,
                              grid: { color: "rgba(200, 200, 200, 0.5)" },
                            },
                          },
                        }}
                        style={{ height: '300px' }}
                      />
                    </div>
                  )}
                </CRow>

                <CRow className="mb-4" style={{ margin: '10px' }}>
                  <h5>Incident SLA Summary</h5>
                  {graphData.incident && (
                    <div style={{ width: "900px" }}>
                      <CChart
                        type="line"
                        data={{
                          labels: graphData.incident.labels,
                          datasets: [
                            {
                              label: "SLA by Incident",
                              data: graphData.incident.values,
                              borderColor: "rgba(54, 162, 235, 1)",
                              backgroundColor: "rgba(54, 162, 235, 0.2)",
                              borderWidth: 3,
                              tension: 0.4,
                            },
                          ],
                        }}
                        options={{
                          responsive: true,
                          scales: {
                            y: {
                              beginAtZero: true,
                              title: {
                                display: true,
                                text: "SLA Compliance",
                              },
                            },
                            x: {
                              title: {
                                display: true,
                                text: "Incidents",
                              },
                            },
                          },
                          plugins: {
                            legend: {
                              display: true,
                              position: "top",
                            },
                          },
                        }}
                        style={{ height: '300px' }}
                      />
                    </div>
                  )}
                </CRow>
                <CRow className="mb-4" style={{ margin: '10px' }}>
                  <h5>User-wise Resolution Time</h5>
                  {chartData.users.length > 0 && (
                    <div style={{ width: "900px" }}>
                      <CChart
                        type="bar"
                        data={{
                          labels: chartData.users,
                          datasets: [
                            {
                              label: "Actual Resolution Time",
                              data: chartData.avgActualTimes,
                              backgroundColor: "#000080",
                              // borderColor: "rgba(54, 162, 235, 1)",
                              borderWidth: 1,
                            },
                            {
                              label: "Expected Resolution Time",
                              data: chartData.avgExpectedTimes,
                              backgroundColor: "#368197",
                              // borderColor: "rgba(255, 99, 132, 1)",
                              borderWidth: 1,
                            },
                          ],
                        }}
                        options={{
                          responsive: true,
                          scales: {
                            y: {
                              beginAtZero: true,
                              title: {
                                display: true,
                                text: "Resolution Time (Minutes)",
                              },
                            },
                            x: {
                              title: {
                                display: true,
                                text: "Users",
                              },
                            },
                          },
                          plugins: {
                            legend: {
                              display: true,
                              position: "top",
                            },
                          },
                        }}
                        style={{ height: '300px' }}
                      />
                    </div>
                  )}
                </CRow>
              </CContainer>
            </CCard>
          </CCol>
        </CRow>
      ) : (
        <>
          <CTable hover responsive>
            <CTableHead>
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell >
                <CTableHeaderCell scope="col">User Name</CTableHeaderCell>
                <CTableHeaderCell scope="col">Ticket ID</CTableHeaderCell>
                <CTableHeaderCell scope="col">Category</CTableHeaderCell>
                <CTableHeaderCell scope="col">Priority</CTableHeaderCell>
                <CTableHeaderCell scope="col">Opened Date</CTableHeaderCell>
                <CTableHeaderCell scope="col">Resolved Date</CTableHeaderCell>
                <CTableHeaderCell scope="col">Resolution Target Time</CTableHeaderCell>
                <CTableHeaderCell scope="col">Resolution Actual Time</CTableHeaderCell>
                <CTableHeaderCell scope="col">SLA Compliance</CTableHeaderCell>
              </CTableRow >
            </CTableHead >
            <CTableBody>
              {slaData.length > 0 ? (
                slaData.map((sla, index) => (
                  <CTableRow key={sla.ticket_id}>
                    <CTableDataCell>{((currentPage || 1) - 1) * (perPage || 10) + index + 1}</CTableDataCell>
                    <CTableDataCell>{sla.user_name}</CTableDataCell>
                    <CTableDataCell>{sla.ticket_id}</CTableDataCell>
                    <CTableDataCell>{sla.category}</CTableDataCell>
                    <CTableDataCell>{sla.priority}</CTableDataCell>
                    <CTableDataCell>{sla.opened_date}</CTableDataCell>
                    <CTableDataCell>{sla.notifications[0]?.resolved_date || ''}</CTableDataCell>
                    <CTableDataCell>{sla.resolve_time_value}</CTableDataCell>
                    <CTableDataCell>{sla.actual_resolved_time}</CTableDataCell>
                    <CTableDataCell>{sla.sla_compliance}</CTableDataCell>
                  </CTableRow>
                ))
              ) : (
                null
              )}
            </CTableBody>
          </CTable >

          <CRow className="mt-3">
            <PaginationForReport
              currentPage={currentPage}
              lastPage={lastPage}
              onPageChange={handlePageChange}
              total={total}
              perPage={handlePerPageValue}
              setPerPage={setPerPage}
              setCurrentPage={setCurrentPage}
            />

            <CCol className="d-flex justify-content-end align-items-baseline">
              <span className="me-2">PerPage Display:</span>
              <CFormSelect
                id="perPage"
                value={perPage}
                onChange={handlePerPageChange}
                className="me-2"
                size="sm"
                style={{ width: '80px' }}
              >
                <option value="5">5</option>
                <option value="10">10</option>
                <option value="15">15</option>
                <option value="20">20</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </CFormSelect>
            </CCol>
          </CRow>

          <CRow className="mb-2">
            <CCol className="text-center">
              Showing {(currentPage - 1) * perPage + 1} to {Math.min(currentPage * perPage, total)} of{' '}
              {total}
            </CCol>
          </CRow>
        </>
      )}
    </>
  )
}

export default SLAReport