import React, { useEffect, useState } from 'react'
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
} from '@coreui/react'

import axios from 'axios'
import { BASE_URL } from '../../config'
import Datetime from 'react-datetime'
import 'react-datetime/css/react-datetime.css'
import moment from 'moment'
import { CChart } from '@coreui/react-chartjs'
import { jsPDF } from 'jspdf';
import "jspdf-autotable";

const CallRankDropdown = ({ callRank, uniqueId, handleRankChange }) => {
  const selectedRank = callRank !== null ? callRank : -1;
  return (
    <select
      name={`call_rank_${uniqueId}`}
      id={`call_rank_${uniqueId}`}
      value={callRank} // Ensure the correct value is selected
      onChange={(e) => handleRankChange(uniqueId, e.target.value)} // Handle changes
    >
      {/* "Not Ranked" Option */}
      <option value="-1" disabled={callRank !== -1}>
        Not Ranked
      </option>

      {/* Rank Options from 1 to 5 */}
      {[0, 1, 2, 3, 4, 5].map((rank) => (
        <option key={rank} value={rank}>
          {rank}
        </option>
      ))}
    </select>
  )
}

const CallCDRreport = () => {
  const [CDRList, setCDRList] = useState([])
  const [startDate, setStartDate] = useState(moment().startOf('day')) // Default to today 00:00:00
  const [endDate, setEndDate] = useState(moment().endOf('day')) // Default to today 23:59:59
  const [page, setPage] = useState(1)
  const [pagination, setPagination] = useState({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })
  const [ingroups, setIngroups] = useState([]);
  const [selectedIngroups, setSelectedIngroups] = useState([]);
  const [campaigns, setCampaigns] = useState([]);
  const [selectedCampaigns, setSelectedCampaigns] = useState('');
  const [users, setUsers] = useState([]);
  const [selectedUsers, setSelectedUsers] = useState('');
  const [chartData, setChartData] = useState({});
  const [lineChartData, setLineChartData] = useState({
    labels: [],
    datasets: [],
  });

  const [dialTypeCounts, setDialTypeCounts] = useState({ Manualbox: 0, DialNext: 0 });
  const [statusChartData, setStatusChartData] = useState({});
  const [individualStatusCounts, setIndividualStatusCounts] = useState({});
  const [cdrData, setCdrData] = useState([]);

  useEffect(() => {
    // Initialize cdrData with CDRList or use an empty array if CDRList is undefined
    setCdrData(CDRList || []);
  }, [CDRList]);

  // Column visibility state
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

  const [callCategory, setCallCategory] = useState('all');
  const [phoneNumber, setPhoneNumber] = useState('');
  const [selectedDuration, setSelectedDuration] = useState('');
  const [durationSeconds, setDurationSeconds] = useState('');
  const [selectedDialType, setDialType] = useState('');
  const [selectedRank, setRank] = useState('');

  // Function to fetch data from API
  const fetchData = async (endpoint, setter) => {
    try {
      const response = await axios.get(`${BASE_URL}/api/${endpoint}`, {
        params: { domain: '192.168.0.120' },
      });
      // console.log(`${endpoint.charAt(0).toUpperCase() + endpoint.slice(1)}:`, response.data);
      setter(response.data);
    } catch (error) {
      console.error(`Error fetching ${endpoint}:`, error);
    }
  };

  useEffect(() => {
    fetchData('ingroups', setIngroups);
    fetchData('campaigns', setCampaigns);
    fetchData('users', setUsers);
  }, []);

  // Handle selection changes
  const handleSelectionChange = (event) => {
    const selectedValues = Array.from(event.target.selectedOptions, option => option.value);
    setSelectedIngroups(selectedValues);
  };

  const handleCampaignChange = (event) => {
    setSelectedCampaigns(event.target.value);
  };

  const handleUserChange = (event) => {
    setSelectedUsers(event.target.value);
  };

  // Handle change event
  const handleCallCategoryChange = (event) => {
    setCallCategory(event.target.value);
  };

  // Function to handle value change
  const handlePhoneChange = (event) => {
    setPhoneNumber(event.target.value);
  };

  // Function to handle the change event
  const handleDurationChange = (event) => {
    setSelectedDuration(event.target.value);
  };

  // Function to handle the change event
  const handleDurationSecChange = (event) => {
    setDurationSeconds(event.target.value);
  };
  //Duration END

  // Function to handle the change event
  const handleDialTypeChange = (event) => {
    setDialType(event.target.value);
  };

  // Function to handle the change event
  const handleRankSearchChange = (event) => {
    setRank(event.target.value);
  };

  const fetchCDRData = async (page = 1, downloadFormat = null) => {
    if (!startDate || !endDate) {
      alert('Please select both start and end dates.')
      return
    }

    // Prepare filter parameters
    const filterParams = {
      start_date: moment(startDate).format('YYYY-MM-DD HH:mm:ss'),
      end_date: moment(endDate).format('YYYY-MM-DD HH:mm:ss'),
      // per_page: pagination.per_page,
      domain: '192.168.0.120',
      // page: page,
      ingroup: selectedIngroups.length > 0 ? selectedIngroups : null,
      campaign: selectedCampaigns !== 'none' ? selectedCampaigns : null,
      search_agent: selectedUsers !== '' ? selectedUsers : null,
      search_phone: phoneNumber !== '' ? phoneNumber : null,
      group_status: callCategory !== 'all' ? callCategory : null,
      search_duration: selectedDuration !== '' ? selectedDuration : null,
      search_second: durationSeconds !== '' ? durationSeconds : null,
      agent_dialed_type: selectedDialType !== '' ? selectedDialType : null,
      search_agent_call_rank: selectedRank !== '' ? selectedRank : null,
    };

    if (downloadFormat === 'csv') {
      filterParams.format = 'csv'; // Set the format to CSV
    } else if (downloadFormat === 'pdf') {
      filterParams.format = 'pdf';
    } else if (downloadFormat !== 'Y') {
      filterParams.per_page = pagination.per_page;
      filterParams.page = page;
    } else {
      filterParams.graph = 'Y'; // Include the graph parameter when fetching data for the graph
    }

    try {
      axios.get(`${BASE_URL}/api/callCDR`, {
        params: filterParams,
        headers: {
          'Content-Type': 'application/json',
          'Access-Control-Allow-Origin': '*',
          'Authorization': `Bearer ${localStorage.getItem('token')}`,
        },
        responseType: downloadFormat === 'csv' ? 'blob' : 'json',
      })
        .then((response) => {
          if (downloadFormat === 'csv') {
            // Handle CSV download
            const now = moment().format('YYYYMMDD_HHmmss'); // Example format: 20231025_142530
            const fileName = `call_cdr_csv_${now}.csv`;

            const blob = new Blob([response.data], { type: 'text/csv' });

            // Create a link element for downloading
            const link = document.createElement('a');
            link.href = window.URL.createObjectURL(blob);

            link.download = fileName; // Set the file name

            // Append link to the body and trigger the download
            document.body.appendChild(link);
            link.click();

            // Cleanup by removing the link from the DOM
            document.body.removeChild(link);

          } else if (downloadFormat === 'pdf') {
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

            // Add a custom header only on the first page
            pdf.text("Your Custom Header Title", 14, 15); // Custom text at the top of the page

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
                  pdf.text("Your Custom Header Title", data.settings.margin.left, 10); // Custom header text on the first page
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

          } else {
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

            if (downloadFormat !== 'Y') {
              setCDRList(callData);
              setPagination(response.data.meta);
            }

          }
        })
    } catch (error) {
      console.error('Error fetching data:', error);
    }
  }

  const handlePageClick = (page) => {
    setPage(page)
  }

  useEffect(() => {
    fetchCDRData(page)
  }, [page])

  const renderPaginationItems = () => {
    const items = []
    for (let i = 1; i <= pagination.last_page; i++) {
      items.push(
        <CPaginationItem
          key={i}
          active={i === pagination.current_page}
          onClick={() => handlePageClick(i)}
        >
          {i}
        </CPaginationItem>
      )
    }
    return items
  }

  const handleRankChange = (uniqueid, newRank) => {
    const updatedCDRList = CDRList.map((cdr) => {
      if (cdr.uniqueid === uniqueid) {
        return { ...cdr, agent_call_rank: newRank === '-1' ? -1 : parseInt(newRank) }; // Update call_rank properly
      }
      return cdr; // Return the same object if no change
    });

    setCDRList(updatedCDRList); // Update local state
  };

  const handleToggleColumn = (columnKey) => {
    setVisibleColumns((prev) => ({
      ...prev,
      [columnKey]: !prev[columnKey],
    }));
  };

  const toggleColumnOptions = () => {
    setShowColumnOptions((prev) => !prev);

  };

  const [showColumnOptions, setShowColumnOptions] = useState(false);

  const [showGraph, setShowGraph] = useState(false);

  // Function to toggle between table and graph
  const handleViewSwitch = () => {
    fetchCDRData(1, 'Y'); // Trigger the API call for the graph view
    setShowGraph((prevShowGraph) => !prevShowGraph);
  };

  const dynamicFieldLabels = []; // Create an empty array to store label names
  if (CDRList.length > 0) {
    const keys = Object.keys(CDRList[0]);
    keys.forEach(key => {
      if (!isNaN(key)) { // Only process dynamic fields like '2001'
        const subfields = Object.keys(CDRList[0][key]);
        subfields.forEach(subfield => {
          const labelName = CDRList[0][key][subfield]?.label_name || subfield;
          dynamicFieldLabels.push(labelName); // Store label names
        });
      }
    });
  }

  const formatDate = (date) => {
    const d = new Date(date);
    const year = d.getFullYear();
    const month = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  };
  
  const [formData, setFormData] = useState(CDRList); // initialData should represent your initial field values

  // const handleInputChange = (e, fieldKey, subfieldKey) => {
  //   const { value } = e.target;
  //   setFormData((prevData) => {
  //     const updatedData = {
  //       ...prevData,
  //       [fieldKey]: {
  //         ...prevData[fieldKey],
  //         [subfieldKey]: {
  //           ...prevData[fieldKey][subfieldKey],
  //           value: value,
  //         },
  //       },
  //     };
  //     console.log('Updated Data:', updatedData); // Log the updated data
  //     return updatedData;
  //   });
  // };
  

  const handleSave = async () => {
    try {
      // Prepare the data for the API call
      const payload = cdrData.map((cdr) => {
        const customFields = Object.keys(cdr)
          .filter((key) => !isNaN(key)) // Filter only dynamic field keys
          .reduce((acc, key) => {
            Object.keys(cdr[key]).forEach((subfieldKey) => {
              const field = cdr[key][subfieldKey];
              acc.push({
                field_name: subfieldKey,
                type: field.type,
                value: field.value,
              });
            });
            return acc;
          }, []);

          console.log("customFields", customFields);
        return {
          list_id: cdr.list_id,
          uniqueid: cdr.uniqueid,
          custom_fields: customFields,
          domain: '192.168.0.120',
        };
      });

      // Make the API call
      const response = await axios.post(`${BASE_URL}/api/insert-update-custom`, payload);
      console.log('Save successful:', response.data);
    } catch (error) {
      console.error('Error saving data:', error);
    }
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
                <CCol xs={3}>
                  <CFormSelect
                    size="sm"
                    id="multi-select"
                    multiple
                    label="Inbound Group"
                    value={selectedIngroups} // Controlled select value
                    onChange={handleSelectionChange} // Handle selection changes
                  >
                    <option value={'all'}>-- All --</option>
                    {ingroups.map((option, index) => (
                      <option key={index} value={option.group_id} title={`${option.group_id} - ${option.group_name}`}>
                        {option.group_id} - {option.group_name}
                      </option>
                    ))}
                  </CFormSelect>
                </CCol>
                <CCol xs={3}>
                  <CFormSelect
                    size="sm"
                    className="mb-3"
                    label="Campaign"
                    value={selectedCampaigns} // Bind the value of the select
                    onChange={handleCampaignChange} // Handle change event
                  >
                    <option value={'none'}>-- NONE --</option>
                    <option value={'all'}>-- All --</option>
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
                    value={callCategory} // Bind the value of the select
                    onChange={handleCallCategoryChange} // Handle change event
                  >
                    <option value={'all'}>All</option>
                    <option value="ans_call">Answered Calls</option>
                    <option value="abn_call">Abandoned Calls</option>
                  </CFormSelect>
                </CCol>
                <CCol xs={2}>
                  <CFormSelect
                    size="sm"
                    className="mb-3"
                    label="Agent"
                    value={selectedUsers} // Bind the value of the select
                    onChange={handleUserChange} // Handle change event
                  >
                    <option value={''}>Select User</option>
                    {users.map((option, index) => (
                      <option key={index} value={option.user}>
                        {option.user}
                      </option>
                    ))}
                  </CFormSelect>

                  {/* phone number */}
                  <CFormInput
                    type="text"
                    size="sm"
                    label="Phone Number"
                    value={phoneNumber}
                    onChange={handlePhoneChange}
                  />
                </CCol>
              </CRow>

              <CRow className="mb-3">
                <CCol xs={2}>
                  <CFormSelect
                    size="sm"
                    className="mb-3"
                    label="Duration"
                    value={selectedDuration} // Bind the value of the select
                    onChange={handleDurationChange} // Handle change event
                  >
                    <option value={''}>Select Duration</option>
                    <option value="1">&lt;=</option>
                    <option value="2">&gt;=</option>
                  </CFormSelect>

                </CCol>
                <CCol xs="auto" className="d-flex align-items-center">
                  <span>-</span> {/* Add a dash between */}
                </CCol>

                <CCol xs={2}>
                  <CFormInput
                    type="text"
                    size="sm"
                    label="Seconds"
                    maxLength="3"
                    value={durationSeconds}
                    onChange={handleDurationSecChange}
                  />
                </CCol>

                <CCol xs={3}>
                  <CFormSelect
                    size="sm"
                    className="mb-3"
                    label="Agent Dialed Type"
                    value={selectedDialType} // Bind the value of the select
                    onChange={handleDialTypeChange} // Handle change event
                  >
                    <option value={'all'}>All</option>
                    <option value="DialNext">DialNext</option>
                    <option value="ManualBox">ManualBox</option>
                  </CFormSelect>
                </CCol>

                <CCol xs={3}>
                  <CFormSelect
                    size="sm"
                    className="mb-3"
                    label="Call Rank"
                    value={selectedRank} // Bind the value of the select
                    onChange={handleRankSearchChange} // Handle change event
                  >
                    <option value={''}>Select Rank</option>
                    <option value="-1">Not Ranked</option>
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                  </CFormSelect>
                </CCol>
              </CRow>

              {/* Search Button */}
              <CButton color="primary" onClick={() => fetchCDRData(1)}>
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
                  onClick={() => fetchCDRData(1, 'csv')}
                >
                  CSV
                </CButton>
                <CButton color="primary" className="mb-4 me-2" size="sm"
                  onClick={() => fetchCDRData(1, 'pdf')}>
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

                  {dynamicFieldLabels.map((label, index) => (
                    <CTableHeaderCell key={index}>{label}</CTableHeaderCell>
                  ))}
                  <CTableHeaderCell>Play Download Recording</CTableHeaderCell>
                  <CTableHeaderCell>Save</CTableHeaderCell>
                </CTableRow>
              </CTableHead>
              <CTableBody>
                {CDRList.length > 0 ? (
                  CDRList.map((cdr, index) => (
                    <CTableRow key={cdr.uniqueid}>
                      {/* First column: serial number */}
                      <CTableDataCell>{(page - 1) * pagination.per_page + index + 1}</CTableDataCell>

                      {/* Dynamically render visible columns, excluding play_download and save */}
                      {Object.keys(visibleColumns).map(
                        (columnKey) =>
                          visibleColumns[columnKey] &&
                          columnKey !== 'play_download' &&
                          columnKey !== 'save' && (
                            <CTableDataCell key={columnKey}>
                              {columnKey === 'agent_call_rank' ? (
                                <CallRankDropdown
                                  callRank={cdr.agent_call_rank}
                                  uniqueId={cdr.uniqueid}
                                  handleRankChange={handleRankChange}
                                />
                              ) : (
                                cdr[columnKey] || ''
                              )}
                            </CTableDataCell>
                          )
                      )}

                      {/* Render all custom fields based on their index and structure */}
                      {Object.keys(cdr).map((fieldKey) => {
                        if (!isNaN(fieldKey)) { // Check if the fieldKey is dynamic (e.g., '2001')
                          const customFields = cdr[fieldKey]; // Access the dynamic field object
                          return Object.keys(customFields).map((subfieldKey) => {
                            const customField = customFields[subfieldKey];
                            const fieldType = customField.type;
                            const fieldValue = customField.value || ''; // Default to an empty string if no value
                            const label = customField.label_name || ''; // Get the label name
                            const isEditable = customField.editable === 'Y';

                            return (
                              <CTableDataCell key={subfieldKey}>
                                {isEditable ? (
                                  (() => {
                                    switch (fieldType) {
                                      case 'TEXT':
                                        return (
                                          <CFormInput
                                            type="text"
                                            value={fieldValue}
                                            onChange={(e) => handleInputChange(e, fieldKey, subfieldKey)} // Update handler with the correct key
                                          />
                                        );
                                      case 'SELECT':
                                        // Define your options here based on your data
                                        const options = [
                                          { value: 'option1', label: 'Option 1' },
                                          { value: 'option2', label: 'Option 2' },
                                          // Add more options dynamically if needed
                                        ];
                                        return (
                                          <CFormSelect
                                            value={fieldValue}
                                            onChange={(e) => handleInputChange(e, fieldKey, subfieldKey)} // Update handler with the correct key
                                          >
                                            {options.map((option) => (
                                              <option key={option.value} value={option.value}>
                                                {option.label}
                                              </option>
                                            ))}
                                          </CFormSelect>
                                        );
                                      case 'DATE':
                                        return (
                                          <CFormInput
                                            type="date"
                                            value={fieldValue}
                                            // value={formatDate(formData[fieldKey][subfieldKey].value)}
                                            onChange={(e) => handleInputChange(e, fieldKey, subfieldKey)} // Update handler with the correct key
                                          />
                                        );
                                      default:
                                        return (
                                          <CFormInput
                                            type="text"
                                            value={fieldValue}
                                            onChange={(e) => handleInputChange(e, fieldKey, subfieldKey)} // Update handler with the correct key
                                          />
                                        );
                                    }
                                  })()
                                ) : (
                                  // Display value for non-editable fields
                                  <span>{fieldValue}</span>
                                )}
                              </CTableDataCell>
                            );
                          });
                        }
                        return null; // Return null if the fieldKey is not dynamic
                      })}

                      {/* Play and Save buttons rendered last */}
                      <CTableDataCell>
                        <CButton color="primary">Play</CButton>
                      </CTableDataCell>
                      <CTableDataCell>
                        <CButton
                          color="success"
                          onClick={handleSave}
                        >
                          Save
                        </CButton>
                      </CTableDataCell>
                    </CTableRow>
                  ))
                ) : (
                  <CTableRow>
                    <CTableDataCell colSpan="28" className="text-center">
                      No data available
                    </CTableDataCell>
                  </CTableRow>
                )}
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

      {/* Pagination */}
      {!showGraph ? (
        <CRow className="mt-3">
          <CCol>
            <CPagination align="center" aria-label="Page navigation">
              <CPaginationItem
                disabled={pagination.current_page === 1}
                onClick={() => handlePageClick(pagination.current_page - 1)}
              >
                Previous
              </CPaginationItem>
              {renderPaginationItems()}
              <CPaginationItem
                disabled={pagination.current_page === pagination.last_page}
                onClick={() => handlePageClick(pagination.current_page + 1)}
              >
                Next
              </CPaginationItem>
            </CPagination>
          </CCol>
        </CRow>
      ) : (
        null
      )}
    </>
  )
}

export default CallCDRreport