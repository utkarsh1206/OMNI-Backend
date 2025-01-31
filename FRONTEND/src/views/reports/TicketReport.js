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
  CContainer,
  CModal,
  CModalHeader,
  CModalTitle,
  CModalBody,
  CModalFooter,
  CProgress
} from "@coreui/react";
import Datetime from 'react-datetime'
import 'react-datetime/css/react-datetime.css'
import axios from 'axios';
import { BASE_URL } from '../../config';
import { AuthContext } from '../../context/AuthContext'
import moment from 'moment'
import PaginationForReport from '../../components/PaginationForReport';
import { CChart } from '@coreui/react-chartjs'
import { formatDateWithTime } from '../../utils/DateUtils'
import { CIcon } from '@coreui/icons-react';
import * as icon from '@coreui/icons'

const TicketReport = () => {
  const { authToken } = useContext(AuthContext)
  const [startDate, setStartDate] = useState(moment().startOf('day')) // Default to today 00:00:00
  const [endDate, setEndDate] = useState(moment().endOf('day')) // Default to today 23:59:59
  const [tickets, setTickets] = useState([]);
  const [currentPage, setCurrentPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [total, setTotal] = useState(0)
  const [perPage, setPerPage] = useState(10)
  const [pagination, setPagination] = useState({
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    total: 0,
  });

  const [showGraph, setShowGraph] = useState(true);
  const [graphData, setGraphData] = useState({
    category: null,
    priority: null,
    status: null,
    weekday: null,
  });
  const [statusFilter, setStatusFilter] = useState('all');
  const [priorityFilter, setPriorityFilter] = useState('all');

  const [isModalOpen, setIsModalOpen] = useState(false);
  const [ticketJourneyData, setTicketJourneyData] = useState(null);

  const handleSearch = async (pageNumber = currentPage, perPageValue = perPage) => {
    const startDateStr = startDate.format('YYYY-MM-DD HH:mm:ss');
    const endDateStr = endDate.format('YYYY-MM-DD HH:mm:ss');

    try {
      const response = await axios.get(`${BASE_URL}/api/ticketReport`, {
        params: {
          startDate: startDateStr,
          endDate: endDateStr,
          status: statusFilter === 'all' ? undefined : statusFilter,
          priority: priorityFilter === 'all' ? undefined : priorityFilter,
          page: pageNumber,
          perPage: perPageValue,
        },
        headers: {
          Authorization: `Bearer ${authToken}`,
        },
      });
      setTickets(response.data.data);
      setCurrentPage(response.data.meta.current_page)
      setLastPage(response.data.meta.last_page)
      setTotal(response.data.meta.total)
      setPerPage(response.data.meta.per_page)

      fetchGraphData(startDateStr, endDateStr, statusFilter, priorityFilter);
    } catch (error) {
      console.error('Error fetching tickets:', error);
    }
  }

  const handleCSVDownload = async (pageNumber = currentPage, perPageValue = perPage) => {
    try {
      const startDateStr = startDate.format('YYYY-MM-DD HH:mm:ss');
      const endDateStr = endDate.format('YYYY-MM-DD HH:mm:ss');

      const response = await axios.get(`${BASE_URL}/api/ticketReport/csv`, {
        params: {
          startDate: startDateStr,
          endDate: endDateStr,
          status: statusFilter === 'all' ? undefined : statusFilter,
          priority: priorityFilter === 'all' ? undefined : priorityFilter,
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
      const fileName = `ticket_report_csv_${now}.csv`;

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

  const fetchGraphData = async (startDateStr, endDateStr, statusFilter, priorityFilter) => {
    try {
      const params = {
        startDate: startDateStr,
        endDate: endDateStr,
        status: statusFilter === 'all' ? undefined : statusFilter,
        priority: priorityFilter === 'all' ? undefined : priorityFilter,
      };

      const headers = { Authorization: `Bearer ${authToken}` };

      const [categoryRes, priorityRes, statusRes, weekdayRes] = await Promise.all([
        axios.get(`${BASE_URL}/api/ticket/summary/category`, { headers, params }),
        axios.get(`${BASE_URL}/api/ticket/summary/priority`, { headers, params }),
        axios.get(`${BASE_URL}/api/ticket/summary/status`, { headers, params }),
        axios.get(`${BASE_URL}/api/ticket/summary/weekday`, { headers, params }),
      ]);

      setGraphData({
        category: mapCategoryData(categoryRes.data.data),
        priority: mapPriorityData(priorityRes.data.data),
        status: mapStatusData(statusRes.data.data),
        weekday: mapWeekdayData(weekdayRes.data.data),
      });
    } catch (error) {
      console.error('Error fetching graph data:', error);
    }
  };

  const mapCategoryData = (data) => {
    const labels = data.map((item) => item.Category);
    const values = data.map((item) => item['Total Tickets']);
    return { labels, values };
  };

  const mapPriorityData = (data) => {
    const labels = data.map((item) => item.Priority);
    const values = data.map((item) => item['Total Tickets']);
    return { labels, values };
  };

  const mapStatusData = (data) => {
    const labels = data.map((item) => item.Status);
    const values = data.map((item) => item['Total Tickets']);
    return { labels, values };
  };

  const mapWeekdayData = (data) => {
    const labels = data.map((item) => item.Weekday);
    const values = data.map((item) => item['Total Tickets']);
    return { labels, values };
  };

  const handleGraphToggle = () => {
    setShowGraph(!showGraph);
  };

  useEffect(() => {
    fetchGraphData();
    handleSearch();
  }, []);

  const handleTicketJourneyClick = async (ticketId) => {
    try {
      const response = await axios.get(`${BASE_URL}/api/notification?supportticket_id=${ticketId}`, {
        headers: { Authorization: `Bearer ${authToken}` },
      });
      setTicketJourneyData(response.data.data);
      setIsModalOpen(true);
    } catch (error) {
      console.error('Error fetching ticket journey data:', error);
    }
  };

  return (
    <>
      <style>
        {`
          .timeline {
            position: relative;
            padding: 20px 0;
            margin-left: 20px;
            border-left: 2px solid #e0e0e0;
          }
          
          .timeline-item {
            position: relative;
            margin-bottom: 20px;
            padding-left: 20px;
          }
          
          .timeline-item:last-child {
            margin-bottom: 0;
          }
          
          .timeline-icon {
            position: absolute;
            left: -10px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #e0e0e0;
         
          }
          
          .timeline-icon.info {
            background-color: #17a2b8;
          }
          
          .timeline-icon.primary {
            background-color: #007bff;
          }
          
          .timeline-icon.danger {
            background-color: #dc3545;
          }
          
          .timeline-content {
            margin-left: 10px;
          }
          
          .timeline-title {
            font-weight: bold;
          }
          
          .text-muted {
            color: #6c757d;
          }
          
          .line {
            position: absolute;
            left: 3px;
            top: 30px;
            width: 2px;
            height: 74px;
            background-color: #e0e0e0;
          }
          
          .timeline-item:last-child .line {
            display: none;
          }
          
        `}
      </style>

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
                  <label>Status</label>
                  <CFormSelect
                    value={statusFilter}
                    onChange={(e) => setStatusFilter(e.target.value)}
                  >
                    <option value="all">Select Status</option>
                    <option value="Unassigned">Unassigned</option>
                    <option value="Open">Open</option>
                    <option value="Waiting on Customer">Waiting on Customer</option>
                    <option value="Waiting on Third Party">Waiting on Third Party</option>
                    <option value="Resolved">Resolved</option>
                    <option value="Closed">Closed</option>
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
                    <h5>Status Summary</h5>
                    {graphData.status && (
                      <div style={{ width: '600px' }}>
                        <CChart
                          type="doughnut"
                          data={{
                            labels: graphData.status.labels,
                            datasets: [
                              {
                                data: graphData.status.values,
                                backgroundColor: ['#e75874', '#be1558', '#fbcbc9', '#e2d810', '#6b7b8c'],
                                // borderColor: ['#FF0000', '#0000FF', '#FFFF00'],
                              },
                            ],
                          }}
                          options={{
                            responsive: true,
                            maintainAspectRatio: false,
                          }}
                          style={{ height: '300px' }}
                        />
                      </div>
                    )}
                  </CCol>

                  <CCol>
                    <h5>Weekday Summary</h5>
                    {graphData.weekday && (
                      <div style={{ width: '600px' }}>
                        <CChart
                          type="line"
                          data={{
                            labels: graphData.weekday.labels,
                            datasets: [
                              {
                                label: 'Tickets by Weekday',
                                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                                borderColor: '#1e847f',
                                pointBackgroundColor: '#1e847f',
                                pointBorderColor: '#000000',
                                data: graphData.weekday.values,
                                tension: 0.4, // Smooth the line curve
                              },
                            ],
                          }}
                          options={{
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                              legend: {
                                display: true,
                                position: 'top',
                              },
                              tooltip: {
                                callbacks: {
                                  label: function (tooltipItem) {
                                    return `${tooltipItem.raw} Tickets`;
                                  },
                                },
                              },
                            },
                            elements: {
                              line: {
                                tension: 0.4,
                              },
                              point: {
                                radius: 3,
                              },
                            },
                            scales: {
                              x: {
                                title: {
                                  display: true,
                                  text: 'Weekdays',
                                },
                              },
                              y: {
                                title: {
                                  display: true,
                                  text: 'Number of Tickets',
                                },
                                beginAtZero: true,
                              },
                            },
                          }}
                          style={{ height: '300px', backgroundColor: 'transparent' }}
                        />
                      </div>
                    )}
                  </CCol>
                </CRow>
                <CRow className="mb-4" style={{ margin: '10px' }}>
                  <h5>Category Summary</h5>
                  {graphData.category && (
                    <div style={{ width: '900px' }}>
                      <CChart
                        type="bar"
                        data={{
                          labels: graphData.category.labels,
                          datasets: [
                            {
                              label: 'Tickets by Category',
                              data: graphData.category.values,
                              backgroundColor: [
                                'rgba(100, 149, 237, 1)',
                              ],
                              borderColor: [
                                'rgba(70, 96, 137, 1)',
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
                              grid: { color: 'rgba(200, 200, 200, 0.5)' },
                              beginAtZero: true,
                            },
                          },
                        }}
                        style={{ height: '300px' }}
                      />
                    </div>
                  )}
                </CRow>
                <CRow className="mb-4" style={{ margin: '10px' }}>
                  <h5>Priority Summary</h5>
                  {graphData.priority && (
                    <div style={{ width: '900px' }}>
                      <CChart
                        type="bar"
                        data={{
                          labels: graphData.priority.labels,
                          datasets: [
                            {
                              label: 'Tickets by Priority',
                              data: graphData.priority.values,
                              backgroundColor: [
                                'rgba(72, 161, 183, 1)',
                              ],
                              borderColor: [
                                'rgba(54, 129, 151, 1)',
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
                              grid: { color: 'rgba(200, 200, 200, 0.5)' },
                              beginAtZero: true,
                            },
                          },
                        }}
                        style={{ height: '300px' }}
                      />
                    </div>
                  )}
                </CRow>

              </CContainer >
            </CCard>
          </CCol>
        </CRow>
      ) : (
        // Table View
        <>
          <CTable hover responsive>
            <CTableHead>
              <CTableRow>
                <CTableHeaderCell scope="col">#</CTableHeaderCell >
                <CTableHeaderCell scope="col">Ticket ID</CTableHeaderCell>
                <CTableHeaderCell scope="col">Customer Name</CTableHeaderCell>
                <CTableHeaderCell scope="col">Category</CTableHeaderCell>
                <CTableHeaderCell scope="col">Priority</CTableHeaderCell>
                <CTableHeaderCell scope="col">Status</CTableHeaderCell>
                <CTableHeaderCell scope="col">Created Date</CTableHeaderCell>
                <CTableHeaderCell scope="col">Last Updated</CTableHeaderCell>
                <CTableHeaderCell scope="col">Assigned Agent</CTableHeaderCell>
                <CTableHeaderCell scope="col">Resolution Time</CTableHeaderCell>
                <CTableHeaderCell scope="col">Escalation</CTableHeaderCell>
                <CTableHeaderCell scope="col">Ticket Journey</CTableHeaderCell>
              </CTableRow >
            </CTableHead >
            <CTableBody>
              {tickets.length > 0 ? (
                tickets.map((ticket, index) => (
                  <CTableRow key={ticket.ticketId}>
                    <CTableDataCell>{(currentPage - 1) * perPage + index + 1}</CTableDataCell>
                    <CTableDataCell>{ticket.ticketId}</CTableDataCell>
                    <CTableDataCell>{ticket.customerName}</CTableDataCell>
                    <CTableDataCell>{ticket.category}</CTableDataCell>
                    <CTableDataCell>{ticket.priority}</CTableDataCell>
                    <CTableDataCell>{ticket.status}</CTableDataCell>
                    <CTableDataCell>{ticket.createdDate}</CTableDataCell>
                    <CTableDataCell>{ticket.lastUpdated}</CTableDataCell>
                    <CTableDataCell>{ticket.assignedAgent}</CTableDataCell>
                    <CTableDataCell>{ticket.resolutionTime}</CTableDataCell>
                    <CTableDataCell>{ticket.escalation}</CTableDataCell>
                    <CTableDataCell>
                      <CButton color="info" onClick={() => handleTicketJourneyClick(ticket.id)}>
                        View Journey
                      </CButton>
                    </CTableDataCell>
                  </CTableRow>
                ))
              ) : (
                null
              )}
            </CTableBody>
          </CTable >

          <CModal visible={isModalOpen} onClose={() => setIsModalOpen(false)}>
            <CModalHeader>
              <CModalTitle>Ticket Journey</CModalTitle>
            </CModalHeader>
            <CModalBody style={{ maxHeight: '400px', overflowY: 'auto' }}>
              {ticketJourneyData ? (
                <div className="max-w-2xl mx-auto mt-8 p-4">
                  <h4>Support Ticket Status Progress</h4>
                  <div className="flex flex-col items-start mb-4 mt-4">
                    {ticketJourneyData.map((log, index) => (
                      <div className="timeline-item" key={log.id}>
                        <div className={`timeline-icon ${log.color}`}><CIcon icon={icon[log.icon]} customClassName="status-icon" /></div>
                        <div className="timeline-content">
                          <span><b>{log.status}</b></span>
                          <p>{log.comment}</p>
                          <small className="text-muted">
                            {new Date(log.created_at).toLocaleString()}
                          </small>
                        </div>
                        {index < ticketJourneyData.length - 1 && <div className="line"></div>}
                      </div>
                    ))}
                  </div>
                </div>
              ) : (
                <p>No journey data available</p>
              )
              }
            </CModalBody>
            <CModalFooter>
              <CButton color="secondary" onClick={() => setIsModalOpen(false)}>
                Close
              </CButton>
            </CModalFooter>
          </CModal>

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
  );
}

export default TicketReport