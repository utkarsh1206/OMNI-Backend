import React, { useEffect, useState, useContext } from 'react'
import {
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CCol,
  CContainer,
  CFormCheck,
  CRow,
  CTable,
  CTableBody,
  CTableDataCell,
  CTableHead,
  CTableHeaderCell,
  CTableRow,
  CPagination,
  CPaginationItem,
  CFormSelect,
  CAlert,
  CModal,
  CModalHeader,
  CModalBody,
  CModalFooter,
} from '@coreui/react'
import axios from 'axios'
import { BASE_URL } from '../../config'
import Datetime from 'react-datetime'
import 'react-datetime/css/react-datetime.css'
import moment from 'moment'
import { jsPDF } from 'jspdf';
import "jspdf-autotable";
import { CChart } from '@coreui/react-chartjs'
import { AuthContext } from '../../context/AuthContext'

const AgentActivityReport = () => {
  const { authToken } = useContext(AuthContext)
  const [startDate, setStartDate] = useState(moment().startOf('day')) // Default to today 00:00:00
  const [endDate, setEndDate] = useState(moment().endOf('day')) // Default to today 23:59:59
  const [selectedUsers, setSelectedUsers] = useState([]);
  const [users, setUsers] = useState([]);
  const [campaigns, setCampaigns] = useState([]);
  const [selectedCampaigns, setSelectedCampaigns] = useState('');
  const [selectedFormat, setSelectedFormat] = useState('');
  const [showColumnOptions, setShowColumnOptions] = useState(false);
  const [validationErrors, setValidationErrors] = useState({
    startDate: '',
    endDate: '',
    users: '',
  });
  const [data, setData] = useState([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState(null);
  const [pagination, setPagination] = useState({
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
  });
  const [visibleDetails, setVisibleDetails] = useState(null);
  const [selectedTransitionData, setSelectedTransitionData] = useState(null);
  const [totalTransition, setTotalTransition] = useState(null);
  const [viewMode, setViewMode] = useState('table');

  const validateForm = () => {
    let isValid = true;
    let errors = {
      startDate: '',
      endDate: '',
      users: '',
    };

    if (!startDate || !endDate) {
      errors.startDate = 'Start date is required.';
      errors.endDate = 'End date is required.';
      isValid = false;
    } else if (startDate.isAfter(endDate)) {
      errors.startDate = 'Start date cannot be after end date.';
      errors.endDate = 'End date cannot be before start date.';
      isValid = false;
    }

    if (!selectedUsers || selectedUsers.length === 0) {
      errors.users = 'At least one user must be selected.';
      isValid = false;
    }

    setValidationErrors(errors);
    return isValid;
  };

  // Fetch users for the user filter dropdown
  useEffect(() => {
    const fetchUsers = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/api/usersList`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        });
        setUsers(response.data);
      } catch (error) {
        setError('Error fetching users');
      }
    };
    fetchUsers();
  }, []);

  useEffect(() => {
    const fetchCampaigns = async () => {
      try {
        const response = await axios.get(`${BASE_URL}/api/campaigns`, {
          headers: {
            Authorization: `Bearer ${authToken}`,
          },
        });
        setCampaigns(response.data);
      } catch (error) {
        setError('Error fetching users');
      }
    };
    fetchCampaigns();
  }, []);

  // Column visibility state
  const [visibleColumns, setVisibleColumns] = useState({
    user: true,
    extension: true,
    campaign_id: true,
    user_group: true,
    event_date: true,
    logged_in_duration: true,
    details: true,
  });

  const columnNames = {
    user: 'User',
    extension: 'Extension',
    campaign_id: 'Campaign',
    user_group: 'User Group',
    event_date: 'Date',
    logged_in_duration: 'Logged in Duration',
    details: 'Details',
  };

  const toggleColumnOptions = () => {
    setShowColumnOptions((prev) => !prev);
  };

  const handleToggleColumn = (columnKey) => {
    setVisibleColumns((prev) => ({
      ...prev,
      [columnKey]: !prev[columnKey],
    }));
  };

  const handleFormatChange = (event) => {
    setSelectedFormat(event.target.value);
  };

  const [chartData, setChartData] = useState({
    labels: [],
    datasets: [{
      data: [],
      backgroundColor: [],
      borderColor: [],
      borderWidth: 2,
    }],
  });

  const [userChartData, setUserChartData] = useState({})

  const fetchData = async (page = 1, format = '') => {
    if (!validateForm()) {
      return;
    }

    setLoading(true);
    setError('');
    try {
      const params = {
        start_date: startDate.format('YYYY-MM-DD HH:mm:ss'),
        end_date: endDate.format('YYYY-MM-DD HH:mm:ss'),
        users: selectedUsers,
        page,
      };

      if (format === 'csv' || format === 'pdf' || format === 'graph') {
        params.format = format;
      }

      if (!['graph', 'csv', 'pdf'].includes(format)) {
        params.per_page = pagination.perPage;
      }

      if (selectedCampaigns) {
        params.campaign = selectedCampaigns;
      }

      if (selectedFormat) {
        params.format = selectedFormat;
        delete params.per_page;
      }

      const response = await axios.get(`${BASE_URL}/api/agent-activity`, {
        params,
        headers: {
          Authorization: `Bearer ${authToken}`,
        },
        responseType: selectedFormat === 'csv' ? 'blob' : 'json',
      });

      if (format === 'csv') {
        // Handle the CSV download
        const now = moment().format('YYYYMMDD_HHmmss'); // Example format: 20231025_142530
        const fileName = `agent_activity_csv_${now}.csv`;

        const blob = new Blob([response.data], { type: 'text/csv' });

        // Create a link element for downloading
        const link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);

        link.download = fileName;

        // Append link to the body and trigger the download
        document.body.appendChild(link);
        link.click();

        // Cleanup by removing the link from the DOM
        document.body.removeChild(link);
      } else if (format === 'pdf') {
        if (!response.data || !response.data.data || response.data.data.length === 0) {
          console.log('No data available for PDF generation.');
          return;
        }

        const allRecordsData = response.data.data;
        const pdf = new jsPDF();

        allRecordsData.forEach((record, index) => {
          const userDetails = [
            [
              record.user || '',
              record.extension || '',
              record.campaign_id || '',
              record.user_group || '',
              record.event_date ? moment(record.event_date).format('YYYY-MM-DD HH:mm:ss') : '',
              record.logged_in_duration || ''
            ]
          ];

          let yPosition = index === 0 ? 30 : pdf.lastAutoTable.finalY + 20;

          // Define the header for user details
          pdf.autoTable({
            startY: yPosition,
            head: [["USER", "EXTENSION", "CAMPAIGN", "USER GROUP", "DATE", "LOGGED IN DURATION"]],
            body: userDetails,
            theme: 'grid',
            margin: { top: 10, left: 10, right: 10 },
            styles: { cellPadding: 2, fontSize: 10 }
          });

          // Update yPosition after user details table
          yPosition = pdf.lastAutoTable.finalY + 10;

          // Transition details table headers
          const columns = ["DATE/TIME", "EVENT", "PAUSE CODE", "PAUSE DURATION", "PREVIEW DURATION", "WAIT DURATION", "DIALING DURATION", "TALK DURATION", "DISPO DURATION", "STATUS"];
          const transitionDetails = record.transition_details || {};
          const transitionData = transitionDetails.transition_data || [];

          // Prepare rows for transition details
          const rows = transitionData.map(event => [
            event.event_time || '',
            event.event || '',
            event.pause_code || '',
            event.pause_duration || '',
            event.preview_duration || '',
            event.wait_duration || '',
            event.dial_duration || '',
            event.talk_duration || '',
            event.dispo_duration || '',
            event.status || ''
          ]);

          // Add totals as the last row
          const totals = transitionDetails.totals || {};
          rows.push([
            "TOTAL",
            "-", "-",
            totals.total_pause || "00:00:00",
            totals.total_preview || "00:00:00",
            totals.total_wait || "00:00:00",
            totals.total_dial || "00:00:00",
            totals.total_talk || "00:00:00",
            totals.total_dispo || "00:00:00",
            "-"
          ]);

          // Add transition details table including the total row
          pdf.autoTable({
            head: [columns],
            body: rows,
            startY: yPosition,
            margin: { top: 10, left: 10, right: 10 },
            theme: 'striped'
          });

          // Update yPosition for the next record
          yPosition = pdf.lastAutoTable.finalY + 20;
        });

        const pdfFileName = `agent_activity_report_${moment().format('YYYYMMDD_HHmmss')}.pdf`;
        pdf.save(pdfFileName);

      } else if (format === 'graph') {
        try {
          const data = response.data.data;

          // ###################### OVERALL TOTAL DURATION GRAPH ######################
          let totals = {
            total_pause: '00:00:00',
            total_preview: '00:00:00',
            total_wait: '00:00:00',
            total_dial: '00:00:00',
            total_talk: '00:00:00',
            total_dispo: '00:00:00',
            total_break: '00:00:00',
            total_hold: '00:00:00',
          };

          data.forEach((record) => {
            const { totals: recordTotals } = record.transition_details;
            totals.total_pause = moment.duration(totals.total_pause).add(moment.duration(recordTotals.total_pause));
            totals.total_preview = moment.duration(totals.total_preview).add(moment.duration(recordTotals.total_preview));
            totals.total_wait = moment.duration(totals.total_wait).add(moment.duration(recordTotals.total_wait));
            totals.total_dial = moment.duration(totals.total_dial).add(moment.duration(recordTotals.total_dial));
            totals.total_talk = moment.duration(totals.total_talk).add(moment.duration(recordTotals.total_talk));
            totals.total_dispo = moment.duration(totals.total_dispo).add(moment.duration(recordTotals.total_dispo));
            totals.total_break = moment.duration(totals.total_break).add(moment.duration(recordTotals.total_break));
            totals.total_hold = moment.duration(totals.total_hold).add(moment.duration(recordTotals.total_hold));

          });

          const totalSeconds = moment.duration(totals.total_pause).add(moment.duration(totals.total_preview))
            .add(moment.duration(totals.total_wait)).add(moment.duration(totals.total_dial))
            .add(moment.duration(totals.total_talk)).add(moment.duration(totals.total_dispo)).add(moment.duration(totals.total_break)).add(moment.duration(totals.total_hold)).asSeconds();

          const chartDataValues = [
            moment.duration(totals.total_pause).asSeconds(),
            moment.duration(totals.total_preview).asSeconds(),
            moment.duration(totals.total_wait).asSeconds(),
            moment.duration(totals.total_dial).asSeconds(),
            moment.duration(totals.total_talk).asSeconds(),
            moment.duration(totals.total_dispo).asSeconds(),
            moment.duration(totals.total_break).asSeconds(),
            moment.duration(totals.total_hold).asSeconds(),
          ];

          // Calculate the percentages based on the total seconds
          const percentages = chartDataValues.map(value => {
            const percentage = (value / totalSeconds) * 100;
            return percentage;
          });

          setChartData({
            labels: ['Pause', 'Preview', 'Wait', 'Dial', 'Talk', 'Disposition', 'Break', 'Hold'],
            datasets: [
              {
                label: 'Total Duration (in seconds)',
                backgroundColor: [
                  'rgba(72, 161, 183, 1)',
                  'rgba(255, 165, 0, 1)',
                  'rgba(100, 149, 237, 1)',
                  'rgba(255, 99, 132, 1)',
                  'rgba(255, 159, 64, 1)',
                  'rgba(75, 192, 192, 1)',
                  'rgba(255, 109, 101, 1)',
                  'rgba(163, 0, 245, 1)'
                ],
                borderColor: [
                  'rgba(54, 129, 151, 1)',
                  'rgba(204, 121, 0, 1)',
                  'rgba(70, 96, 137, 1)',
                  'rgba(204, 0, 51, 1)',
                  'rgba(204, 115, 34, 1)',
                  'rgba(54, 128, 128, 1)',
                  'rgba(255, 99, 71, 1)',
                  'rgba(163, 0, 245, 0.42)',
                ],
                data: chartDataValues,
                borderWidth: 2,
              },
            ],
          });

          // Set labels with percentages
          setChartData(prevData => ({
            ...prevData,
            labels: prevData.labels.map((label, index) => {
              return `${label} (${percentages[index].toFixed(2)}%)`; // Add percentage to label
            }),
          }));

          // ###################### USER-WISE TOTAL DURATION GRAPH ######################
          const userTotals = {};
          data.forEach((record) => {
            const { user, logged_in_duration, transition_details } = record;
            const { totals: recordTotals } = transition_details;

            if (!userTotals[user]) {
              userTotals[user] = {
                total_logged_in: moment.duration(logged_in_duration || "00:00:00"),
                total_pause: moment.duration("00:00:00"),
                total_preview: moment.duration("00:00:00"),
                total_dial: moment.duration("00:00:00"),
                total_wait: moment.duration("00:00:00"),
                total_talk: moment.duration("00:00:00"),
                total_dispo: moment.duration("00:00:00"),
                total_break: moment.duration("00:00:00"),
                total_hold: moment.duration("00:00:00"),
              };
            }

            userTotals[user].total_logged_in = userTotals[user].total_logged_in.add(moment.duration(logged_in_duration || "00:00:00"));
            userTotals[user].total_pause = userTotals[user].total_pause.add(moment.duration(recordTotals.total_pause || "00:00:00"));
            userTotals[user].total_preview = userTotals[user].total_preview.add(moment.duration(recordTotals.total_preview || "00:00:00"));
            userTotals[user].total_dial = userTotals[user].total_dial.add(moment.duration(recordTotals.total_dial || "00:00:00"));
            userTotals[user].total_wait = userTotals[user].total_wait.add(moment.duration(recordTotals.total_wait || "00:00:00"));
            userTotals[user].total_talk = userTotals[user].total_talk.add(moment.duration(recordTotals.total_talk || "00:00:00"));
            userTotals[user].total_dispo = userTotals[user].total_dispo.add(moment.duration(recordTotals.total_dispo || "00:00:00"));
            userTotals[user].total_break = userTotals[user].total_break.add(moment.duration(recordTotals.total_break || "00:00:00"));
            userTotals[user].total_hold = userTotals[user].total_hold.add(moment.duration(recordTotals.total_hold || "00:00:00"));
          });

          const userLabels = Object.keys(userTotals);
          const loggedInData = userLabels.map(user => userTotals[user].total_logged_in.asSeconds());
          const pauseData = userLabels.map(user => userTotals[user].total_pause.asSeconds());
          const previewData = userLabels.map(user => userTotals[user].total_preview.asSeconds());
          const dialData = userLabels.map(user => userTotals[user].total_dial.asSeconds());
          const waitData = userLabels.map(user => userTotals[user].total_wait.asSeconds());
          const talkData = userLabels.map(user => userTotals[user].total_talk.asSeconds());
          const dispoData = userLabels.map(user => userTotals[user].total_dispo.asSeconds());
          const breakData = userLabels.map(user => userTotals[user].total_break.asSeconds());
          const holdData = userLabels.map(user => userTotals[user].total_hold.asSeconds());

          setUserChartData({
            labels: userLabels,
            datasets: [
              {
                label: 'Total Login Duration (s)',
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgba(75, 192, 192, 1)',
                data: loggedInData,
                borderWidth: 2,
              },
              {
                label: 'Total Pause Duration (s)',
                backgroundColor: 'rgba(72, 161, 183, 1)',
                borderColor: 'rgba(54, 129, 151, 1)',
                data: pauseData,
                borderWidth: 2,
              },
              {
                label: 'Total Preview Duration (s)',
                backgroundColor: 'rgba(255, 165, 0, 1)',
                borderColor: 'rgba(204, 121, 0, 1)',
                data: previewData,
                borderWidth: 2,
              },
              {
                label: 'Total Dial Duration (s)',
                backgroundColor: 'rgba(100, 149, 237, 1)',
                borderColor: 'rgba(70, 96, 137, 1)',
                data: dialData,
                borderWidth: 2,
              },
              {
                label: 'Total Wait Duration (s)',
                backgroundColor: 'rgba(255, 99, 132, 1)',
                borderColor: 'rgba(204, 0, 51, 1)',
                data: waitData,
                borderWidth: 2,
              },
              {
                label: 'Total talk Duration (s)',
                backgroundColor: 'rgba(255, 159, 64, 1)',
                borderColor: 'rgba(204, 115, 34, 1)',
                data: talkData,
                borderWidth: 2,
              },
              {
                label: 'Total Disposition Duration (s)',
                backgroundColor: 'rgba(75, 192, 192, 1)',
                borderColor: 'rgba(54, 128, 128, 1)',
                data: dispoData,
                borderWidth: 2,
              },
              {
                label: 'Total Break Duration (s)',
                backgroundColor: 'rgba(255, 109, 101, 1)',
                borderColor: 'rgba(255, 99, 71, 1)',
                data: breakData,
                borderWidth: 2,
              },
              {
                label: 'Total Hold Duration (s)',
                backgroundColor: 'rgba(163, 0, 245, 1)',
                borderColor: 'rgba(163, 0, 245, .42)',
                data: holdData,
                borderWidth: 2,
              },
            ],
          });

        } catch (error) {
          console.error('Error fetching data:', error);
        }
      } else {
        setData(response.data.data);
        setPagination({
          currentPage: response.data.meta.current_page,
          lastPage: response.data.meta.last_page,
          perPage: response.data.meta.per_page,
          total: response.data.meta.total,
        });
      }
    } catch (error) {
      setError('Error fetching data');
      console.error("Error generating PDF:", error);
    } finally {
      setLoading(false);
    }
  };

  const handleHover = (event, chartElement) => {
    if (chartElement.length > 0) {
      setActiveIndex(chartElement[0].index); // Set active index on hover
    } else {
      setActiveIndex(null); // Reset active index when hover leaves
    }
  };

  useEffect(() => {
    fetchData(pagination.currentPage);
  }, [pagination.currentPage, users, startDate, endDate]);


  const handleCampaignChange = (event) => {
    setSelectedCampaigns(event.target.value);
  };

  const handlePageClick = (page) => {
    setPagination((prev) => ({
      ...prev,
      currentPage: page,
    }));
  };

  const renderPaginationItems = () => {
    const items = [];
    for (let i = 1; i <= pagination.lastPage; i++) {
      items.push(
        <CPaginationItem
          key={i}
          active={i === pagination.currentPage}
          onClick={() => handlePageClick(i)}
        >
          {i}
        </CPaginationItem>
      );
    }
    return items;
  };

  const handleDetailClick = (transitionDetails) => {
    setSelectedTransitionData(transitionDetails.transition_data);
    setTotalTransition(transitionDetails.totals);
  };

  const handleViewModeChange = (mode) => {
    setViewMode(mode);
  };

  const closeModal = () => {
    setSelectedTransitionData(null);
    setTotalTransition(null);
  };

  const [showGraph, setShowGraph] = useState(false);

  // Function to toggle between table and graph
  const handleViewSwitch = () => {
    setShowGraph(!showGraph); // Toggle view
    if (!showGraph) {
      fetchData(1, 'graph');
    }
    else {
      fetchData(1, '');
    }
  };

  const handleSearch = () => {
    const format = showGraph ? 'graph' : '';
    fetchData(1, format);
  };

  const renderIndividualGraphData = () => {
    if (!selectedTransitionData || selectedTransitionData.length === 0) return null;

    const timeToSeconds = (time) => {
      if (time === "-" || !time) return 0;
      const [hours, minutes, seconds] = time.split(":").map(Number);
      return (hours * 3600) + (minutes * 60) + seconds;
    };

    const labels = selectedTransitionData.map(item => moment(item.event_time).format('YYYY-MM-DD HH:mm:ss'));
    const pauseDurations = selectedTransitionData.map(item => timeToSeconds(item.pause_duration));
    const previewDurations = selectedTransitionData.map(item => timeToSeconds(item.preview_duration));
    const waitDurations = selectedTransitionData.map(item => timeToSeconds(item.wait_duration));
    const dialDurations = selectedTransitionData.map(item => timeToSeconds(item.dial_duration));
    const talkDurations = selectedTransitionData.map(item => timeToSeconds(item.talk_duration));
    const dispoDurations = selectedTransitionData.map(item => timeToSeconds(item.dispo_duration));
    const breakDurations = selectedTransitionData.map(item => timeToSeconds(item.break_duration));
    const holdDurations = selectedTransitionData.map(item => timeToSeconds(item.hold_duration));


    const totalPause = pauseDurations.reduce((acc, cur) => acc + cur, 0);
    const totalPreview = previewDurations.reduce((acc, cur) => acc + cur, 0);
    const totalWait = waitDurations.reduce((acc, cur) => acc + cur, 0);
    const totalDial = dialDurations.reduce((acc, cur) => acc + cur, 0);
    const totalTalk = talkDurations.reduce((acc, cur) => acc + cur, 0);
    const totalDispo = dispoDurations.reduce((acc, cur) => acc + cur, 0);
    const totalBreak = breakDurations.reduce((acc, cur) => acc + cur, 0);
    const totalHold = holdDurations.reduce((acc, cur) => acc + cur, 0);

    const totalDurations = [
      totalPause,
      totalPreview,
      totalWait,
      totalDial,
      totalTalk,
      totalDispo,
      totalBreak,
      totalHold,
    ];

    const totalAll = totalDurations.reduce((acc, cur) => acc + cur, 0);

    // Labels for the total duration chart
    const totalLabels = ['Pause', 'Preview', 'Wait', 'Dial', 'Talk', 'Dispo', 'Break', 'Hold'];

    // Calculate percentage for each category (optional)
    const percentages = totalDurations.map(duration => (duration / totalAll * 100).toFixed(2));

    const formatDuration = (durationInSeconds) => {
      const hours = Math.floor(durationInSeconds / 3600);
      const minutes = Math.floor((durationInSeconds % 3600) / 60);
      const seconds = durationInSeconds % 60;
      return `${hours}:${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    };

    return (
      <div>
        <CChart
          type="doughnut"
          data={{
            labels: totalLabels,
            datasets: [
              {
                label: 'Total Duration (HH:MM:SS)',
                data: totalDurations,
                backgroundColor: [
                  'rgba(255, 45, 85, 1)',
                  'rgba(0, 102, 204, 1)',
                  'rgba(255, 140, 0, 1)',
                  'rgba(255, 94, 77, 1)',
                  'rgba(0, 153, 153, 1)',
                  'rgba(102, 0, 204, 1)',
                  'rgba(255, 109, 101, 1)',
                  'rgba(163, 0, 245, 1)',
                ],
              },
            ],
          }}
          options={{
            responsive: true,
            plugins: {
              legend: {
                position: 'top',
              },
              tooltip: {
                callbacks: {
                  // Customizing the tooltip to show both the duration and percentage
                  label: function (tooltipItem) {
                    const index = tooltipItem.dataIndex;
                    return `${totalLabels[index]}: ${formatDuration(totalDurations[index])} (${percentages[index]}%)`;
                  },
                },
              },
            },
          }}
          style={{ height: '400px', marginTop: '20px' }}
        />
        <CChart
          type="bar"
          data={{
            labels,
            datasets: [
              {
                label: 'Pause Duration',
                data: pauseDurations,
                backgroundColor: 'rgba(255, 45, 85, 1)',
              },
              {
                label: 'Preview Duration',
                data: previewDurations,
                backgroundColor: 'rgba(0, 102, 204, 1)',
              },
              {
                label: 'Wait Duration',
                data: waitDurations,
                backgroundColor: 'rgba(255, 140, 0, 1)',
              },
              {
                label: 'Dial Duration',
                data: dialDurations,
                backgroundColor: 'rgba(255, 94, 77, 1)',
              },
              {
                label: 'Talk Duration',
                data: talkDurations,
                backgroundColor: 'rgba(0, 153, 153, 1)',
              },
              {
                label: 'Dispo Duration',
                data: dispoDurations,
                backgroundColor: 'rgba(102, 0, 204, 1)',
              },
              {
                label: 'Break Duration',
                data: breakDurations,
                backgroundColor: 'rgba(255, 109, 101, 1)',
              },
              {
                label: 'Hold Duration',
                data: holdDurations,
                backgroundColor: 'rgba(163, 0, 245, 1)',
              },
            ],
          }}
          options={{
            responsive: true,
            scales: {
              x: {
                stacked: true,
              },
              y: {
                stacked: true,
                beginAtZero: true,
              },
            },
          }}
          style={{ height: '400px' }}
        />

      </div>

    );
  };

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
              {error && <CAlert color="danger">{error}</CAlert>}
              <CRow className="mb-3">
                <CCol xs={2}>
                  <label>Start Date and Time</label>
                  <Datetime
                    value={startDate}
                    onChange={(date) => setStartDate(date)}
                    dateFormat="YYYY-MM-DD"
                    timeFormat="HH:mm:ss"
                  />
                  {validationErrors.startDate && (
                    <div className="text-danger">{validationErrors.startDate}</div>
                  )}
                </CCol>
                <CCol xs={2}>
                  <label>End Date and Time</label>
                  <Datetime
                    value={endDate}
                    onChange={(date) => setEndDate(date)}
                    dateFormat="YYYY-MM-DD"
                    timeFormat="HH:mm:ss"
                  />
                  {validationErrors.endDate && (
                    <div className="text-danger">{validationErrors.endDate}</div>
                  )}
                </CCol>
                <CCol xs={3}>
                  <label>User</label>
                  <CFormSelect
                    multiple
                    value={selectedUsers}
                    onChange={(e) => setSelectedUsers(Array.from(e.target.selectedOptions, option => option.value))}
                    options={[
                      { label: 'Select User(s)', value: '' }, // Default option
                      ...users.map((user) => ({ label: user.user, value: user.user })),
                    ]}
                  />
                  {validationErrors.users && (
                    <div className="text-danger">{validationErrors.users}</div>
                  )}
                </CCol>
                <CCol xs={3}>
                  <CFormSelect
                    size="sm"
                    className="mb-3"
                    label="Campaign"
                    value={selectedCampaigns}
                    onChange={handleCampaignChange}
                  >
                    <option value={''}>-- All --</option>
                    {campaigns.map((option, index) => (
                      <option key={index} value={option.campaign_id} title={`${option.campaign_id} - ${option.campaign_name}`}>
                        {option.campaign_id} - {option.campaign_name}
                      </option>
                    ))}
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
                  className="mb-4 me-2"
                  size="sm"
                  color="secondary"
                  onClick={toggleColumnOptions}
                >
                  {showColumnOptions ? 'Hide Column Options' : 'Show Column Options'}
                </CButton>
                {showColumnOptions && (
                  <CRow>
                    {Object.keys(visibleColumns).map((columnKey) => (
                      <CCol md='2' className='mb-1'>
                        <CFormCheck
                          key={columnKey}
                          id={columnKey}
                          label={columnNames[columnKey]}
                          checked={visibleColumns[columnKey]}
                          onChange={() => handleToggleColumn(columnKey)}
                        />
                      </CCol>
                    ))}
                  </CRow>
                )}

                <CButton
                  color="primary"
                  className="mb-4 me-2"
                  size="sm"
                  onClick={handleViewSwitch}
                >
                  {showGraph ? 'Show Table View' : 'Show Graph View'}
                </CButton>

                <CButton
                  color="primary"
                  className="mb-4 me-2"
                  size="sm"
                  onClick={() => fetchData(1, 'csv')}
                >
                  CSV
                </CButton>
                <CButton
                  color="primary"
                  className="mb-4 me-2"
                  size="sm"
                  onClick={() => fetchData(1, 'pdf')}
                >
                  PDF
                </CButton>
              </CCol>
            </CRow>
          </CContainer>

          {!showGraph ? (
            <CTable id="report-table" small hover bordered responsive>
              <CTableHead color="light">
                <CTableRow>
                  <CTableHeaderCell scope="col">#</CTableHeaderCell>
                  {Object.keys(visibleColumns).map(
                    (columnKey) =>
                      visibleColumns[columnKey] && (
                        <CTableHeaderCell key={columnKey} scope="col">
                          {columnNames[columnKey]}
                        </CTableHeaderCell>
                      )
                  )}
                </CTableRow>
              </CTableHead>
              <CTableBody>
                {loading ? (
                  <CTableRow>
                    <CTableDataCell colSpan={8} className="text-center">Loading...</CTableDataCell>
                  </CTableRow>
                ) : error ? (
                  <CTableRow>
                    <CTableDataCell colSpan={8} className="text-center text-danger">{error}</CTableDataCell>
                  </CTableRow>
                ) : data.length === 0 ? (
                  <CTableRow>
                    <CTableDataCell colSpan={8} className="text-center">No data available</CTableDataCell>
                  </CTableRow>
                ) : (
                  data.map((item, index) => (
                    <React.Fragment key={item.id || index}>
                      <CTableRow>
                        <CTableDataCell>
                          {(pagination.currentPage - 1) * pagination.perPage + index + 1}
                        </CTableDataCell>
                        {Object.keys(visibleColumns).map(
                          (columnKey) =>
                            visibleColumns[columnKey] && (
                              <CTableDataCell key={columnKey}>
                                {columnKey === 'event_date'
                                  ? moment(item[columnKey]).format('YYYY-MM-DD HH:mm:ss')
                                  : columnKey === 'details' && visibleColumns['details']
                                    ? (
                                      <CButton
                                        color="info"
                                        size="sm"
                                        onClick={() => handleDetailClick(item.transition_details)}
                                      >
                                        Details
                                      </CButton>
                                    )
                                    : item[columnKey] || '-'}
                              </CTableDataCell>
                            )
                        )}
                      </CTableRow>
                    </React.Fragment>
                  ))
                )}
              </CTableBody>
            </CTable>
          ) : (
            <div>
              {loading && <div>Loading...</div>}
              {error && <div className="text-danger">{error}</div>}
              {chartData && chartData.datasets && chartData.datasets[0] && chartData.datasets[0].data.length > 0 && !loading && !error && (
                <div className="chart-container" style={{ justifyContent: 'space-between', width: '100%', maxWidth: '1200px', margin: '0 auto' }}>
                  <div>
                    <CChart
                      type="doughnut"
                      data={chartData}
                      options={{
                        responsive: true,
                        plugins: {
                          legend: { position: 'top' },
                          tooltip: {
                            callbacks: {
                              label: function (tooltipItem) {
                                return `${tooltipItem.label}: ${tooltipItem.raw} seconds`;
                              },
                            },
                          },
                        },
                        elements: {
                          arc: {
                            borderWidth: 10,
                            hoverBorderWidth: 15,
                          },
                        },
                        rotation: -0.5 * Math.PI,
                        onHover: handleHover,
                      }}
                      style={{ height: '400px' }}
                    />
                  </div>
                  <div>
                    <CChart
                      type="line"
                      data={{
                        labels: userChartData.labels,
                        datasets: userChartData.datasets,
                      }}
                      options={{
                        responsive: true,
                        plugins: {
                          legend: {
                            position: 'top',
                          },
                          title: {
                            display: true,
                            text: 'User-Wise Total Duration',
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
                      }}
                      style={{ height: '400px', marginBottom: '40px' }}
                    />
                  </div>
                </div>
              )}
            </div>

          )
          }
        </CCol>
      </CRow>

      {/* Modal to show transition_data */}
      <CModal visible={selectedTransitionData !== null} onClose={closeModal} className="modal-lg">
        <CModalHeader>Transition Data Details</CModalHeader>
        <CModalBody>
          <div className="mb-3">
            <CButton
              color={viewMode === 'table' ? 'primary' : 'secondary'}
              onClick={() => handleViewModeChange('table')}
            >
              View Table
            </CButton>
            <CButton
              color={viewMode === 'graph' ? 'primary' : 'secondary'}
              className="ms-2"
              onClick={() => handleViewModeChange('graph')}
            >
              View Graph
            </CButton>
          </div>

          {viewMode === 'table' ? (
            <CTable small hover bordered responsive>
              <CTableHead color="light">
                <CTableRow>
                  <CTableHeaderCell>#</CTableHeaderCell>
                  <CTableHeaderCell>Event Time</CTableHeaderCell>
                  <CTableHeaderCell>Event</CTableHeaderCell>
                  <CTableHeaderCell>Pause Code</CTableHeaderCell>
                  <CTableHeaderCell>Pause Duration</CTableHeaderCell>
                  <CTableHeaderCell>Preview Duration</CTableHeaderCell>
                  <CTableHeaderCell>Wait Duration</CTableHeaderCell>
                  <CTableHeaderCell>Dial Duration</CTableHeaderCell>
                  <CTableHeaderCell>Talk Duration</CTableHeaderCell>
                  <CTableHeaderCell>Dispo Duration</CTableHeaderCell>
                  <CTableHeaderCell>Break Duration</CTableHeaderCell>
                  <CTableHeaderCell>Hold Duration</CTableHeaderCell>
                  <CTableHeaderCell>Status</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                {selectedTransitionData && selectedTransitionData.length > 0 ? (
                  selectedTransitionData.map((transition, index) => (
                    <CTableRow key={index}>
                      <CTableDataCell>{transition.serial_number}</CTableDataCell>
                      <CTableDataCell>{moment(transition.event_time).format('YYYY-MM-DD HH:mm:ss')}</CTableDataCell>
                      <CTableDataCell>{transition.event}</CTableDataCell>
                      <CTableDataCell>{transition.pause_code || '-'}</CTableDataCell>
                      <CTableDataCell>{transition.pause_duration || '-'}</CTableDataCell>
                      <CTableDataCell>{transition.preview_duration || '-'}</CTableDataCell>
                      <CTableDataCell>{transition.wait_duration || '-'}</CTableDataCell>
                      <CTableDataCell>{transition.dial_duration || '-'}</CTableDataCell>
                      <CTableDataCell>{transition.talk_duration || '-'}</CTableDataCell>
                      <CTableDataCell>{transition.dispo_duration || '-'}</CTableDataCell>
                      <CTableDataCell>{transition.break_duration || '-'}</CTableDataCell>
                      <CTableDataCell>{transition.hold_duration || '-'}</CTableDataCell>
                      <CTableDataCell>{transition.status || '-'}</CTableDataCell>
                    </CTableRow>
                  ))
                ) : (
                  <CTableRow>
                    <CTableDataCell colSpan={11} className="text-center">No transition data available</CTableDataCell>
                  </CTableRow>
                )}

                {totalTransition ? (
                  <CTableRow>
                    <CTableDataCell colSpan={4} className="text-end"><strong>Totals</strong></CTableDataCell>
                    <CTableDataCell>{totalTransition.total_pause || '-'}</CTableDataCell>
                    <CTableDataCell>{totalTransition.total_preview || '-'}</CTableDataCell>
                    <CTableDataCell>{totalTransition.total_wait || '-'}</CTableDataCell>
                    <CTableDataCell>{totalTransition.total_dial || '-'}</CTableDataCell>
                    <CTableDataCell>{totalTransition.total_talk || '-'}</CTableDataCell>
                    <CTableDataCell>{totalTransition.total_dispo || '-'}</CTableDataCell>
                    <CTableDataCell>{totalTransition.total_break || '-'}</CTableDataCell>
                    <CTableDataCell>{totalTransition.total_hold || '-'}</CTableDataCell>
                    <CTableDataCell>-</CTableDataCell>
                  </CTableRow>
                ) : (
                  <CTableRow>
                    <CTableDataCell colSpan={11} className="text-center">No totals data available</CTableDataCell>
                  </CTableRow>
                )}

              </CTableBody>
            </CTable>
          ) : (
            renderIndividualGraphData()
          )}
        </CModalBody>
        <CModalFooter>
          <CButton color="secondary" onClick={closeModal}>Close</CButton>
        </CModalFooter>

        <style jsx>{`
          .modal-dialog {
            max-width: 1200px;
            width: 90%;
          }

          .modal-body {
            max-height: 500px;
            overflow-y: auto;
          }
          
        `}</style>
      </CModal>

      {!showGraph ? (
        <CRow className="mt-3">
          <CCol>
            <CPagination align="center" aria-label="Page navigation">
              <CPaginationItem
                disabled={pagination.currentPage === 1}
                onClick={() => handlePageClick(pagination.currentPage - 1)}
              >
                Previous
              </CPaginationItem>
              {renderPaginationItems()}
              <CPaginationItem
                disabled={pagination.currentPage === pagination.lastPage}
                onClick={() => handlePageClick(pagination.currentPage + 1)}
              >
                Next
              </CPaginationItem>
            </CPagination>
          </CCol>
        </CRow>
      ) : (null)}
    </>
  );
}

export default AgentActivityReport;
