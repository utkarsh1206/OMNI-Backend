import React, { useEffect, useState, useContext } from 'react';
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
  CFormInput
} from '@coreui/react';

import axios from 'axios';
import { BASE_URL } from '../../config';
import Datetime from 'react-datetime';
import 'react-datetime/css/react-datetime.css';
import moment from 'moment';
import { CChart } from '@coreui/react-chartjs';
import { jsPDF } from 'jspdf';
import 'jspdf-autotable';
import { AuthContext } from '../../context/AuthContext'
import PaginationForReport from '../../components/PaginationForReport';


const renderField = (field, value, editable, handleFieldChange, uniqueid) => {
  const { label_name, type, label } = field; // Destructure field object
  switch (type) {
    case 'TEXT':
      if (editable === 'Y') {
        return (
          <CFormInput
            type="text"
            value={value || ''}
            onChange={(e) => handleFieldChange(e, label, uniqueid)} // Update state on change
            // placeholder={label_name} // Use label_name for placeholder
            style={{ width: '100px' }}
          />
        );
      } else {
        return <p>{value}</p>; // Display value if not editable
      }
    case 'DATE':
      if (editable === 'Y') {
        return (
          <input
            type="date"
            value={value || ''}
            onChange={(e) => handleFieldChange(e, label, uniqueid)} // Update state on change
          />
        );
      } else {
        return <p>{value}</p>; // Display value if not editable
      }
    case 'SELECT':
      if (editable === 'Y') {
        return (
          <CFormSelect
            value={value || ''}
            onChange={(e) => handleFieldChange(e, label, uniqueid)}
          >
            <option value="option1">Option 1</option>
            <option value="option2">Option 2</option>
            <option value="option3">Option 3</option>
          </CFormSelect>
        );
      } else {
        return <p>{value}</p>; // Display value if not editable
      }
    default:
      return <p>{value}</p>;
  }
};

const CallCDRreport = () => {
  const { authToken } = useContext(AuthContext)
  const [startDate, setStartDate] = useState(moment().startOf('day')); // Default to today 00:00:00
  const [endDate, setEndDate] = useState(moment().endOf('day')); // Default to today 23:59:59
  const [ingroups, setIngroups] = useState([]);
  const [selectedIngroups, setSelectedIngroups] = useState([]);
  const [campaigns, setCampaigns] = useState([]);
  const [selectedCampaigns, setSelectedCampaigns] = useState('');
  const [users, setUsers] = useState([]);
  const [selectedUsers, setSelectedUsers] = useState('');
  const [callCategory, setCallCategory] = useState('all');
  const [phoneNumber, setPhoneNumber] = useState('');
  const [selectedDuration, setSelectedDuration] = useState('');
  const [durationSeconds, setDurationSeconds] = useState('');
  const [selectedDialType, setDialType] = useState('');
  const [selectedRank, setRank] = useState('');
  const [showColumnOptions, setShowColumnOptions] = useState(false);
  const [showGraph, setShowGraph] = useState(false);
  const [selectedFormat, setSelectedFormat] = useState('');
  const [error, setError] = useState(null);
  const [currentPage, setCurrentPage] = useState(1)
  const [lastPage, setLastPage] = useState(1)
  const [total, setTotal] = useState(0)
  const [perPage, setPerPage] = useState(10)

  const [chartData, setChartData] = useState({});
  const [lineChartData, setLineChartData] = useState({
    labels: [],
    datasets: [],
  });
  const [dialTypeCounts, setDialTypeCounts] = useState({ Manualbox: 0, DialNext: 0 });
  const [statusChartData, setStatusChartData] = useState({});
  const [individualStatusCounts, setIndividualStatusCounts] = useState({});

  const [visibleColumns, setVisibleColumns] = useState({
    list_id: true,
    did: true,
    campaign: true,
    inbound_group: true,
    call_date: true,
    user: true,
    extension: true,
    phone_number: true,
    type_of_call: true,
    ivr_time: true,
    hold_duration: true,
    talk_duration: true,
    dispo_duration: true,
    total_duration: true,
    customer_setup_time: true,
    customer_ringing_time: true,
    transfer_to_agent: true,
    status: true,
    customer_dispo_status: true,
    term_reason: true,
    customer_time: true,
    abandoned_time: true,
    uniqueid: true,
    comments: true,
    agent_call_rank: true,
  });

  const columnNames = {
    list_id: 'List',
    did: 'DID Line',
    campaign: 'Campaign',
    inbound_group: 'Inbound Group',
    call_date: 'Call Date',
    user: 'User',
    extension: 'Extension',
    phone_number: 'Phone Number',
    type_of_call: 'Type of Call',
    ivr_time: 'IVR time',
    hold_duration: 'Hold Duration',
    talk_duration: 'Talk Duration',
    dispo_duration: 'Call Disposition Duration',
    total_duration: 'Total Duration',
    customer_setup_time: 'Customer Setup Time',
    customer_ringing_time: 'Customer Ringing Time',
    transfer_to_agent: 'Transfer To Agent',
    status: 'Status',
    customer_dispo_status: 'Customer Dispo Status',
    term_reason: 'Term Reason',
    customer_time: 'Customer Time',
    abandoned_time: 'Abandoned Time',
    uniqueid: 'UniqueID',
    comments: 'Comments',
    agent_call_rank: 'Call Rank',
  };
  const [CDRList, setCDRList] = useState([])
  const [pagination, setPagination] = useState({
    currentPage: 1,
    lastPage: 1,
    perPage: 10,
    total: 0,
  });


  const fetchData = async (endpoint, setter) => {
    try {
      const response = await axios.get(`${BASE_URL}/api/${endpoint}`, {
        headers: {
          Authorization: `Bearer ${authToken}`,
        },
      });
      setter(response.data);
    } catch (error) {
      console.error(`Error fetching ${endpoint}:`, error);
    }
  };

  useEffect(() => {
    fetchData('ingroups', setIngroups);
    fetchData('campaigns', setCampaigns);
    fetchData('usersList', setUsers);
  }, []);

  const handleChange = (setter) => (event) => {
    setter(event.target.value);
  };

  const handleMultipleSelectionChange = (setter) => (event) => {
    const selectedValues = Array.from(event.target.selectedOptions, (option) => option.value);
    setter(selectedValues);
  };

  const toggleColumnOptions = () => {
    setShowColumnOptions((prev) => !prev);
  };

  const handleFormatChange = (event) => {
    setSelectedFormat(event.target.value);
  }

  const fetchReportData = async (page = 1, format = '') => {
    try {
      const params = {
        start_date: startDate.format('YYYY-MM-DD HH:mm:ss'),
        end_date: endDate.format('YYYY-MM-DD HH:mm:ss'),
        ingroup: selectedIngroups.length > 0 ? selectedIngroups : null,
        campaign: selectedCampaigns !== 'none' ? selectedCampaigns : null,
        search_agent: selectedUsers !== '' ? selectedUsers : null,
        search_phone: phoneNumber !== '' ? phoneNumber : null,
        group_status: callCategory !== 'all' ? callCategory : null,
        search_duration: selectedDuration !== '' ? selectedDuration : null,
        search_second: durationSeconds !== '' ? durationSeconds : null,
        agent_dialed_type: selectedDialType !== '' ? selectedDialType : null,
        search_agent_call_rank: selectedRank !== '' ? selectedRank : null,

      }
      if (format === 'csv' || format === 'pdf' || format === 'graph') {
        params.format = format;
      }

      if (!['graph', 'csv', 'pdf'].includes(format)) {
        params.per_page = pagination.perPage;
        params.page = pagination.page;
      }

      if (selectedFormat) {
        params.format = selectedFormat;
        delete params.per_page;
      }

      const response = await axios.get(`${BASE_URL}/api/callCDR`, {
        params,
        headers: {
          Authorization: `Bearer ${authToken}`,
        },
        responseType: selectedFormat === 'csv' ? 'blob' : 'json',
      });

      if (format === 'csv') {
        const now = moment().format('YYYYMMDD_HHmmss'); // Example format: 20231025_142530
        const fileName = `call_cdr_csv_${now}.csv`;

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
          alert('No data available for PDF generation.');
          return;
        }

        const allRecordsData = response.data.data;
        const pdf = new jsPDF();

        const columns = Object.keys(columnNames)
          .filter(key => visibleColumns[key])
          .map(key => ({
            title: columnNames[key],
            dataKey: key
          }));

        // Prepare the data for the PDF, including only visible columns
        const pdfData = allRecordsData.map(record => {
          const formattedRecord = {};
          Object.keys(record).forEach(key => {
            if (visibleColumns[key]) { // Check if the column should be visible
              formattedRecord[key] = record[key];
            }
          });
          return formattedRecord;
        });
        const pageWidth = pdf.internal.pageSize.width;
        const tableMargin = 10; // Left and right margin for the table
        const availableWidth = pageWidth - 2 * tableMargin;
        const columnCount = columns.length;
        const columnWidth = availableWidth / columnCount; // Set the width of each column

        let fontSize = 4; // Default font size

        // If the table width is too wide, reduce the font size dynamically
        if (columnWidth * columnCount > availableWidth) {
          fontSize = Math.floor(fontSize * (availableWidth / (columnWidth * columnCount)));
        }

        // Set the font size to the adjusted value
        pdf.setFontSize(fontSize);

        // Use autoTable to add a table with lines and adjust for font size
        pdf.autoTable({
          head: [columns.map(col => col.title)], // Only titles for headers
          body: pdfData.map(record => columns.map(col => record[col.dataKey])), // Extract values in the correct order
          startY: 20, // Table starts at Y position 20
          margin: { horizontal: tableMargin }, // Add left/right margin
          styles: {
            fontSize: fontSize, // Apply the dynamic font size
            overflow: 'linebreak', // Allow text to wrap within the cell
            lineColor: [0, 0, 0], // Black color for the lines
            lineWidth: 0.1, // Thin lines for table borders
            halign: 'center', // Align text to the center horizontally
            valign: 'middle', // Vertically align the text in cells
          },
          headStyles: {
            fillColor: [255, 255, 255], // White background for the header
            textColor: [0, 0, 0], // Black text for the header
            lineWidth: 0.1, // Thin lines for the header borders
          },
          bodyStyles: {
            textColor: [0, 0, 0], // Black text for the body
            lineColor: [0, 0, 0], // Black lines for the table body
            lineWidth: 0.1, // Thin lines for table borders
            minCellHeight: 8, // Prevent cells from becoming too small
          },
          columnStyles: {
            // Optionally set column widths if needed
            0: { cellWidth: columnWidth }, // Column 1 width
            1: { cellWidth: columnWidth }, // Column 2 width
            2: { cellWidth: columnWidth }, // Column 3 width
            // Add more columns as necessary
          },
          pageBreak: 'auto', // Automatically break pages when needed
          didDrawPage: (data) => {
            // Custom logic to add a header only on the first page
            if (data.pageCount === 1) {
              pdf.setFontSize(10);
            }
          },
          // Add word wrapping within the cell
          overflow: 'linebreak',
          didDrawCell: (data) => {
            // Ensure that long text is wrapped correctly without breaking words
            if (data.row.index !== 0 && data.cell.raw) {
              const cellText = data.cell.raw;
              if (cellText && cellText.length > 0) {
                // Allow word break by inserting soft hyphen if required
                const cellWidth = data.cell.width;
                const cellHeight = data.cell.height;
                const fontSize = pdf.getFontSize();
                const textWidth = pdf.getTextWidth(cellText);

                // Adjust font size if text does not fit
                if (textWidth > cellWidth) {
                  // Decrease font size until text fits
                  let reducedFontSize = fontSize;
                  while (pdf.getTextWidth(cellText) > cellWidth && reducedFontSize > 6) {
                    reducedFontSize -= 0.5; // Decrease font size slightly until it fits
                    pdf.setFontSize(reducedFontSize);
                  }
                }
              }
            }
          }
        });

        const pdfFileName = `call_cdr_report_${moment().format('YYYYMMDD_HHmmss')}.pdf`;
        pdf.save(pdfFileName);
      } else if (format === 'graph') {
        const callData = response.data.data;

        const userTotals = callData.reduce((acc, call) => {
          const user = call.user;
          const callType = call.type_of_call;

          if (!acc[user]) {
            acc[user] = { Answered: 0, Abandoned: 0, Total: 0 };
          }

          if (callType === 'Answered') {
            acc[user].Answered += 1;
          } else if (callType === 'Abandoned') {
            acc[user].Abandoned += 1;
          }

          acc[user].Total += 1;
          return acc;
        }, {});

        // 2. Sort users by total answered calls
        const sortedUsers = Object.entries(userTotals)
          .sort(([, a], [, b]) => b.Answered - a.Answered) // Sort by answered calls
          .slice(0, 5); // Get top 5

        // 3. Gather data for top users and others
        const topUsersData = {};
        let othersAnswered = 0;
        let othersAbandoned = 0;

        // Prepare data for top users
        sortedUsers.forEach(([user]) => {
          const userCallData = callData.filter(call => call.user === user);
          const userWiseCounts = userCallData.reduce((acc, call) => {
            const date = moment(call.call_date).format('YYYY-MM-DD');
            if (!acc[date]) {
              acc[date] = { Answered: 0, Abandoned: 0 };
            }
            if (call.type_of_call === 'Answered') {
              acc[date].Answered += 1;
            } else if (call.type_of_call === 'Abandoned') {
              acc[date].Abandoned += 1;
            }
            return acc;
          }, {});

          topUsersData[user] = userWiseCounts;
        });

        // 4. Aggregate others
        Object.entries(userTotals).forEach(([user, totals]) => {
          if (!sortedUsers.some(([topUser]) => topUser === user)) {
            othersAnswered += totals.Answered;
            othersAbandoned += totals.Abandoned;
          }
        });

        // Prepare datasets for the chart
        const labels = Array.from(new Set(callData.map(call => moment(call.call_date).format('YYYY-MM-DD'))));
        const datasets = [];

        // Add top users data
        Object.entries(topUsersData).forEach(([user, dates]) => {
          const answeredData = labels.map(label => (dates[label]?.Answered || 0));
          const abandonedData = labels.map(label => (dates[label]?.Abandoned || 0));

          datasets.push({
            label: `${user} - Answered`,
            data: answeredData,
            borderColor: '#13C16B',
            backgroundColor: 'rgba(19, 193, 107, 0.2)',
            fill: true,
            tension: 0.4,
          });

          datasets.push({
            label: `${user} - Abandoned`,
            data: abandonedData,
            borderColor: '#F78323',
            backgroundColor: 'rgba(247, 131, 35, 0.2)',
            fill: true,
            tension: 0.4,
          });
        });

        // Add "Others" data
        const othersData = labels.map(label => othersAnswered); // Total answered for "Others"
        const othersAbandonedData = labels.map(label => othersAbandoned); // Total abandoned for "Others"

        datasets.push({
          label: 'Others - Answered',
          data: othersData,
          borderColor: '#007bff', // Color for Others
          backgroundColor: 'rgba(0, 123, 255, 0.2)',
          fill: true,
          tension: 0.4,
        });

        datasets.push({
          label: 'Others - Abandoned',
          data: othersAbandonedData,
          borderColor: '#dc3545', // Color for Others
          backgroundColor: 'rgba(220, 53, 69, 0.2)',
          fill: true,
          tension: 0.4,
        });

        // 5. Update chart data
        setLineChartData({
          labels: labels, // Ensure labels are correctly formatted as date strings
          datasets: datasets,
          options: {
            interaction: {
              mode: 'nearest',
              intersect: true,
            },
            scales: {
              x: {
                type: 'time',
                time: {
                  unit: 'month', // Group by month
                  tooltipFormat: 'YYYY-MM-DD', // Tooltip shows full date
                  displayFormats: {
                    month: 'MMM YYYY', // X-axis displays as "Jan 2024", "Feb 2024", etc.
                  },
                },
                ticks: {
                  autoSkip: true,
                  maxTicksLimit: 12, // Show one label per month
                  maxRotation: 0, // Keep labels horizontal for clarity
                },
              },
              y: {
                beginAtZero: true,
              },
            },
          },
        });

        // Count occurrences of "Answered" and "Abandoned"
        const callTypeCounts = callData.reduce((acc, call) => {
          const callType = call.type_of_call; // Get the type of call
          if (callType === 'Answered' || callType === 'Abandoned') {
            acc[callType] = (acc[callType] || 0) + 1; // Increment the count for the call type
          }
          return acc;
        }, {});

        // Prepare chart data
        const newChartData = {
          labels: Object.keys(callTypeCounts), // e.g., ["Answered", "Abandoned"]
          datasets: [
            {
              data: Object.values(callTypeCounts), // e.g., [10, 5]
              backgroundColor: ['#13C16B', '#F78323'], // Match colors with call types
              hoverBackgroundColor: ['#57CC83', '#F89B4F'],
            },
          ],
        };

        // Set the chart data for rendering
        setChartData(newChartData);

        // Initialize counts for Agent Dialed Type
        const dialTypeCounts = {
          Manualbox: 0,
          DialNext: 0,
        };

        // Calculate counts for Agent Dialed Type
        callData.forEach(call => {
          const dialType = call.agent_dialed_type; // Adjust this based on your actual data field
          if (dialType === 'Manualbox') {
            dialTypeCounts.Manualbox += 1;
          } else if (dialType === 'DialNext') {
            dialTypeCounts.DialNext += 1;
          }
        });

        setDialTypeCounts(dialTypeCounts); // Update state with the counts

        const statusCounts = callData.reduce((acc, call) => {
          const status = call.status; // Get the status
          acc[status] = (acc[status] || 0) + 1; // Increment the count for the status
          return acc;
        }, {});


        // Prepare chart data for status
        const statusChartData = {
          labels: Object.keys(statusCounts),
          datasets: [
            {
              backgroundColor: '#00ADEF',
              data: Object.values(statusCounts),
            },
          ],
        };

        // Set state for charts
        setChartData(newChartData);
        setStatusChartData(statusChartData); // New state for status chart
        setIndividualStatusCounts(statusCounts);

      } else {
        setCDRList(response.data.data);
        setCurrentPage(response.data.meta.current_page)
        setLastPage(response.data.meta.last_page)
        setTotal(response.data.meta.total)
        setPerPage(response.data.meta.per_page)
      }

    } catch (error) {
      setError('Error fetching data');
    }

  }

  const handleViewSwitch = () => {
    setShowGraph(!showGraph); // Toggle view
    if (!showGraph) {
      fetchReportData(1, 'graph');
    }
    else {
      fetchReportData(1, '');
    }
  };

  const handleSearch = () => {
    const format = showGraph ? 'graph' : '';
    fetchReportData(1, format);
  };

  const handleToggleColumn = (columnKey) => {
    setVisibleColumns((prev) => ({
      ...prev,
      [columnKey]: !prev[columnKey],
    }));
  };

  const handleRankChange = (uniqueid, newRank) => {
    setCDRList((prevList) =>
      prevList.map((item) =>
        item.uniqueid === uniqueid
          ? { ...item, agent_call_rank: newRank }
          : item
      )
    );
  };

  const [formData, setFormData] = useState({});

  const handleFieldChange = (e, field, uniqueid) => {
    const { value } = e.target;

    // Update formData state
    setFormData((prevData) => ({
      ...prevData,
      [uniqueid]: {
        ...prevData[uniqueid],
        [field]: value, // Update the specific field for the given uniqueid
      },
    }));
  };

  const handleSave = async (uniqueId, listId, closecallid) => {
    const itemToSave = formData[uniqueId];
    console.log('Saving item:', itemToSave);

    const customQuery = {};

    Object.entries(itemToSave).forEach(([key, value]) => {
      if (typeof value === 'string') {
        customQuery[key] = value;
      }
      if (typeof value === 'object' && value !== null && 'value' in value) {
        customQuery[key] = value.value;
      }
    });

    customQuery.uniqueid = uniqueId;
    customQuery.closecallid = closecallid;


    // Create the data to send to the API
    const dataToSave = {
      // uniqueid: uniqueId,
      list_id: listId,
      custom_query: customQuery,
    };

    console.log("dataToSave ", dataToSave)

    try {
      // Make the API request using axios (POST method)
      const response = await axios.post(`${BASE_URL}/api/insert-update-custom`, dataToSave, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authToken}`,
        }
      });

      alert(response.data.message);
      console.log('Save successful:', response.data.message);

    } catch (error) {
      // Handle error
      console.error('API request failed:', error);
      // You can show an error message or handle the error in the UI
    }
  };

  useEffect(() => {
    fetchReportData(pagination.page);
  }, [pagination.page, pagination.perPage]);

  const handlePageChange = (newPage) => {
    setPagination((prev) => ({ ...prev, page: newPage }));
  };

  const handlePerPageValue = (perPage) => {
    setPerPage(perPage)  // Update the current page when pagination changes
  }

  const handlePerPageChange = (e) => {
    setPagination((prev) => ({
      ...prev,
      perPage: Number(e.target.value),
      page: 1, // Reset to first page when perPage is changed
    }));
  };

  return (
    <>
      <CRow>
        <CCol xs={12}>
          <CCard className="mb-2">
            <CCardHeader>
              <div>Search for Filters</div>
            </CCardHeader>
            <CCardBody>
              <CRow className="mb-3">
                {/* Date and Time Filters */}
                <CCol xs={2}>
                  <label>Start Date and Time</label>
                  <Datetime
                    value={startDate}
                    onChange={setStartDate}
                    dateFormat="YYYY-MM-DD"
                    timeFormat="HH:mm:ss"
                  />
                </CCol>
                <CCol xs={2}>
                  <label>End Date and Time</label>
                  <Datetime
                    value={endDate}
                    onChange={setEndDate}
                    dateFormat="YYYY-MM-DD"
                    timeFormat="HH:mm:ss"
                  />
                </CCol>

                {/* Inbound Group Filter */}
                <CCol xs={3}>
                  <CFormSelect
                    size="sm"
                    id="multi-select"
                    multiple
                    label="Inbound Group"
                    value={selectedIngroups}
                    onChange={handleMultipleSelectionChange(setSelectedIngroups)}
                  >
                    <option value="all">-- All --</option>
                    {ingroups.map((option, index) => (
                      <option key={index} value={option.group_id} title={`${option.group_id} - ${option.group_name}`}>
                        {option.group_id} - {option.group_name}
                      </option>
                    ))}
                  </CFormSelect>
                </CCol>

                {/* Campaign and Call Category Filters */}
                <CCol xs={3}>
                  <CFormSelect
                    size="sm"
                    className="mb-3"
                    label="Campaign"
                    value={selectedCampaigns}
                    onChange={handleChange(setSelectedCampaigns)}
                  >
                    <option value="none">-- NONE --</option>
                    <option value="all">-- All --</option>
                    {campaigns.map((option, index) => (
                      <option key={index} value={option.campaign_id} title={`${option.campaign_id} - ${option.campaign_name}`}>
                        {option.campaign_id} - {option.campaign_name}
                      </option>
                    ))}
                  </CFormSelect>

                  <CFormSelect
                    size="sm"
                    className="mb-3"
                    label="Call Category"
                    value={callCategory}
                    onChange={handleChange(setCallCategory)}
                  >
                    <option value="all">All</option>
                    <option value="ans_call">Answered Calls</option>
                    <option value="abn_call">Abandoned Calls</option>
                  </CFormSelect>
                </CCol>

                {/* Agent and Phone Number Filters */}
                <CCol xs={2}>
                  <CFormSelect
                    size="sm"
                    className="mb-3"
                    label="Agent"
                    value={selectedUsers}
                    onChange={handleChange(setSelectedUsers)}
                  >
                    <option value="">Select User</option>
                    {users.map((option, index) => (
                      <option key={index} value={option.user}>
                        {option.user}
                      </option>
                    ))}
                  </CFormSelect>

                  <CFormInput
                    type="text"
                    size="sm"
                    label="Phone Number"
                    value={phoneNumber}
                    onChange={handleChange(setPhoneNumber)}
                  />
                </CCol>
              </CRow>

              <CRow className="mb-3">
                {/* Duration Filters */}
                <CCol xs={2}>
                  <CFormSelect
                    size="sm"
                    className="mb-3"
                    label="Duration"
                    value={selectedDuration}
                    onChange={handleChange(setSelectedDuration)}
                  >
                    <option value="">Select Duration</option>
                    <option value="1">&lt;=</option>
                    <option value="2">&gt;=</option>
                  </CFormSelect>
                </CCol>
                <CCol xs="auto" className="d-flex align-items-center">
                  <span>-</span>
                </CCol>
                <CCol xs={2}>
                  <CFormInput
                    type="text"
                    size="sm"
                    label="Seconds"
                    maxLength="3"
                    value={durationSeconds}
                    onChange={handleChange(setDurationSeconds)}
                  />
                </CCol>

                {/* Dial Type and Call Rank Filters */}
                <CCol xs={3}>
                  <CFormSelect
                    size="sm"
                    className="mb-3"
                    label="Agent Dialed Type"
                    value={selectedDialType}
                    onChange={handleChange(setDialType)}
                  >
                    <option value="all">All</option>
                    <option value="DialNext">DialNext</option>
                    <option value="ManualBox">ManualBox</option>
                  </CFormSelect>
                </CCol>

                <CCol xs={3}>
                  <CFormSelect
                    size="sm"
                    className="mb-3"
                    label="Call Rank"
                    value={selectedRank}
                    onChange={handleChange(setRank)}
                  >
                    <option value="">Select Rank</option>
                    <option value="-1">Not Ranked</option>
                    {[0, 1, 2, 3, 4, 5].map((rank) => (
                      <option key={rank} value={rank}>{rank}</option>
                    ))}
                  </CFormSelect>
                </CCol>
              </CRow>

              {/* Search Button */}
              <CButton color="primary" onClick={handleSearch}>
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
            <CRow>
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
                  onClick={() => fetchReportData(1, 'csv')}
                >
                  CSV
                </CButton>
                <CButton
                  color="primary"
                  className="mb-4 me-2"
                  size="sm"
                  onClick={() => fetchReportData(1, 'pdf')}
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

                  <CTableHeaderCell>#</CTableHeaderCell>
                  {Object.keys(visibleColumns).map(
                    (columnKey) =>
                      visibleColumns[columnKey] && (
                        <CTableHeaderCell key={columnKey}>
                          {columnNames[columnKey]}
                        </CTableHeaderCell>
                      )
                  )}

                  {CDRList.length > 0 &&
                    Object.keys(CDRList[0]).map((key, index) => {
                      if (typeof CDRList[0][key] === 'object' && CDRList[0][key] !== null) {
                        // For each dynamic field like 2001, 2005, etc., loop through its nested keys
                        return Object.keys(CDRList[0][key]).map((nestedKey) => (
                          <CTableHeaderCell key={`${index}-${nestedKey}`}>
                            {CDRList[0][key][nestedKey].label_name}
                          </CTableHeaderCell>
                        ));
                      }
                      return null;
                    })}
                  {/* Save button column */}
                  <CTableHeaderCell>Save</CTableHeaderCell>
                </CTableRow>


              </CTableHead>

              <CTableBody>
                {CDRList.map((item, index) => (
                  <React.Fragment key={item.id || index}>
                    <CTableRow key={item.closecallid}>
                      <CTableDataCell>
                        {/* {(pagination.currentPage - 1) * pagination.perPage + index + 1} */}
                        {(currentPage - 1) * perPage + index + 1}
                      </CTableDataCell>
                      {Object.keys(visibleColumns).map((columnKey) =>
                        visibleColumns[columnKey] && (
                          <CTableDataCell key={columnKey}>
                            {columnKey === 'agent_call_rank' ? (
                              <CFormSelect
                                style={{ width: 'auto' }}
                                value={item.agent_call_rank === -1 ? 'Not Ranked' : String(item.agent_call_rank)}
                                onChange={(e) =>
                                  handleRankChange(
                                    item.uniqueid,
                                    e.target.value === 'Not Ranked' ? -1 : parseInt(e.target.value)
                                  )
                                }
                              >
                                <option value="Not Ranked">Not Ranked</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                              </CFormSelect>
                            ) : (
                              item[columnKey] || '-'
                            )}
                          </CTableDataCell>
                        )
                      )}

                      {Object.entries(item).map(([key, value]) => {
                        if (value && typeof value === 'object') {
                          return (
                            <React.Fragment key={key}>
                              {Object.entries(value).map(([subKey, field]) => (
                                <CTableDataCell key={subKey}>
                                  {renderField(field, formData[item.uniqueid]?.[field.label] || field.value, field.editable, handleFieldChange, item.uniqueid)}
                                </CTableDataCell>
                              ))}
                            </React.Fragment>
                          );
                        }
                        return null;
                      })}

                      <CTableDataCell>
                        <CButton
                          color="success"
                          onClick={() => handleSave(item.uniqueid, item.list_id, item.closecallid)}
                        >
                          Save
                        </CButton>
                      </CTableDataCell>
                    </CTableRow>
                  </React.Fragment>
                ))}
              </CTableBody>
            </CTable>
          ) : (
            <div>
              <div style={{ display: 'flex', justifyContent: 'space-between', width: '100%', maxWidth: '1200px', margin: '0 auto' }}>
                <div className="chart-wrapper" style={{ width: '30%' }}>
                  {/* Graph View */}
                  <h6>Call Type Distribution</h6>
                  <CChart
                    type="doughnut"
                    data={chartData}
                    options={{
                      responsive: true,
                      plugins: {
                        legend: {
                          position: 'top',
                        },
                        tooltip: {
                          enabled: true, // Tooltips are still enabled for hover info
                        },
                      },
                      // Plugin to draw the text inside each slice
                      onAfterDraw: (chart) => {
                        const ctx = chart.ctx;
                        chart.data.datasets.forEach((dataset, i) => {
                          const meta = chart.getDatasetMeta(i);
                          meta.data.forEach((element, index) => {
                            // Get the value to display
                            const dataValue = dataset.data[index];

                            // Calculate the position for the text
                            const position = element.tooltipPosition();

                            // Set font and text alignment
                            ctx.font = 'bold 16px Arial';
                            ctx.fillStyle = 'white'; // You can change this color based on the slice color
                            ctx.textAlign = 'center';
                            ctx.textBaseline = 'middle';

                            // Draw the text (value) inside the slice
                            ctx.fillText(dataValue, position.x, position.y);
                          });
                        });
                      }
                    }}
                  />
                </div>

                <div style={{ marginTop: '20px', width: '100%', display: 'flex', justifyContent: 'space-between' }}>
                  <CChart
                    type="line"
                    data={lineChartData}
                    options={{
                      responsive: true,
                      maintainAspectRatio: false,
                      scales: {
                        x: {
                          title: {
                            display: true,
                            text: 'Date',
                          },
                        },
                        y: {
                          beginAtZero: true,
                          title: {
                            display: true,
                            text: 'Number of Calls',
                          },
                        },
                      },
                      plugins: {
                        legend: {
                          position: 'top',
                        },
                        tooltip: {
                          enabled: true,
                          mode: 'index',
                          intersect: false,
                        },
                      },
                    }}
                    style={{ height: '400px' }}
                  />
                </div>
              </div>
              <div className="chart-wrapper" style={{ width: '90%' }}>
                <h6>Status Distribution</h6>
                <CChart
                  type="bar"
                  data={statusChartData}
                  style={{ height: '400px' }}
                  options={{
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                      legend: { position: 'top' },
                      title: { display: true, text: 'Status Count Distribution' },
                    },
                  }}
                />
              </div>
            </div>
          )}
        </CCol>
      </CRow>

      {!showGraph ? (
        <CRow className="mt-3">
          <PaginationForReport
            currentPage={currentPage}
            lastPage={lastPage}
            onPageChange={handlePageChange}
            total={total}
            perPage={handlePerPageValue} // Make sure to define and pass itemsPerPage here
            setPerPage={setPerPage}
            setCurrentPage={setCurrentPage}
          />

          <CCol className="d-flex justify-content-end align-items-baseline">
            <span className="me-2">PerPage Display:</span>
            <CFormSelect
              id="perPage"
              value={pagination.perPage}
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

      ) : (
        null
      )}
    </>
  );
};

export default CallCDRreport;
