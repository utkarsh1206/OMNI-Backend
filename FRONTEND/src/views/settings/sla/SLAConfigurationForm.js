import React, { useState, useEffect, useContext } from 'react'
import { useParams, useNavigate } from 'react-router-dom'
import axios from 'axios'
import {
  CForm,
  CFormLabel,
  CFormInput,
  CRow,
  CCol,
  CFormSwitch,
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CBadge,
  CTable,
  CModal,
  CModalHeader,
  CModalBody,
  CTableRow,
  CTableHead,
  CTableHeaderCell,
  CTableBody,
  CModalFooter,
  CTableDataCell,
} from '@coreui/react'
import { AuthContext } from '../../../context/AuthContext'
import { BASE_URL } from '../../../config'
import Datetime from "react-datetime"
import { extractTime } from '../../../utils/DateUtils'
import 'react-datetime/css/react-datetime.css'

const SLAConfigurationForm = () => {
  const { id } = useParams() // Get Config ID from URL if present
  const isEditMode = Boolean(id)
  const navigate = useNavigate()
  const { authToken } = useContext(AuthContext)

  const [businessHours, setBusinessHours] = useState({
    name: '',
    is24Hours: false,
    startTime: '',
    endTime: '',
    weekOffDays: [],
  })

  const [targets, setTargets] = useState([
    {
      type: 'Critical',
      responseTime: { value: 0 },
      alertBeforeResponse: { value: 0 },
      resolveTime: { value: 0 },
      alertBeforeResolve: { value: 0 },
      enabled: true,
    },
    {
      type: 'Low',
      responseTime: { value: 0 },
      alertBeforeResponse: { value: 0 },
      resolveTime: { value: 0 },
      alertBeforeResolve: { value: 0 },
      enabled: true,
    },
    {
      type: 'Normal',
      responseTime: { value: 0 },
      alertBeforeResponse: { value: 0 },
      resolveTime: { value: 0 },
      alertBeforeResolve: { value: 0 },
      enabled: true,
    },
    {
      type: 'Medium',
      responseTime: { value: 0 },
      alertBeforeResponse: { value: 0 },
      resolveTime: { value: 0 },
      alertBeforeResolve: { value: 0 },
      enabled: true,
    },
    {
      type: 'High',
      responseTime: { value: 0 },
      alertBeforeResponse: { value: 0 },
      resolveTime: { value: 0 },
      alertBeforeResolve: { value: 0 },
      enabled: true,
    },
  ])

  const [levels, setLevels] = useState({
    1: targets, // Initial level (Level 1) starts with default targets
  })

  const [holidays, setHolidays] = useState([])
  const [showModal, setShowModal] = useState(false)

  useEffect(() => {
    if (isEditMode) {
      // Fetch data for the SLA config
      axios
        .get(`${BASE_URL}/api/sla-settings/${id}`, {
          headers: {
            'Content-Type': 'application/json',
            'Access-Control-Allow-Origin': '*',
            Authorization: `Bearer ${authToken}`,
          },
        })
        .then((response) => {
          const data = response.data
          // Populate form fields with pre-fetched data
          setBusinessHours({
            name: data.name,
            description: data.description,
            is24Hours: data.is_24_hours,
            // startTime: data.start_time || '',
            // endTime: data.end_time || '',
            startTime: data.is_24_hours ? '00:00:00' : data.start_time || '',
            endTime: data.is_24_hours ? '23:59:59' : data.end_time || '',
            // weekOffDays: data.off_days.map((offDay) => offDay.day),
            weekOffDays: data.off_days || [],
          })
          setHolidays(data.holidays || [])

          // Correctly handle the targets
          const fetchedTargets = Object.keys(data.targets).reduce((acc, level) => {
            const targetsForLevel = targets.map((target) => {
              const foundTarget = data.targets[level].find((t) => t.priority_type === target.type)
              return {
                ...target,
                enabled: foundTarget ? true : false,
                responseTime: {
                  value: foundTarget ? foundTarget.response_time_value : 0,
                },
                alertBeforeResponse: {
                  value: foundTarget ? foundTarget.alert_before_response_value : 0,
                },
                resolveTime: {
                  value: foundTarget ? foundTarget.resolve_time_value : 0,
                },
                alertBeforeResolve: {
                  value: foundTarget ? foundTarget.alert_before_resolve_value : 0,
                },
              }
            })
            acc[level] = targetsForLevel
            return acc
          }, {})

          setLevels(fetchedTargets) // Set the levels with fetched targets
        })
        .catch((error) => {
          console.error('Error fetching SLA data:', error)
          alert('Failed to fetch SLA data.')
        })
    }
  }, [id, isEditMode])

  const handleAddTargetLevel = () => {
    const levelCount = Object.keys(levels).length
    if (levelCount < 4) {
      // Limit to a maximum of 4 levels
      const newLevel = levelCount + 1
      setLevels((prevLevels) => ({
        ...prevLevels,
        [newLevel]: JSON.parse(JSON.stringify(targets)), // Create a deep copy of the targets array
      }))
    } else {
      alert('You can add up to 4 levels only.')
    }
  }

  const handleLevelInputChange = (level, index, field, subField, value) => {
    const newLevels = { ...levels }
    newLevels[level][index][field][subField] = value
    setLevels(newLevels)
  }

  const handleLevelSwitchChange = (level, index) => {
    const newLevels = { ...levels }
    newLevels[level][index].enabled = !newLevels[level][index].enabled
    setLevels(newLevels)
  }

  const handleSave = () => {
    const formattedTargets = Object.keys(levels).reduce((acc, level) => {
      acc[level] = levels[level]
        .filter((target) => target.enabled) // Include only enabled targets
        .map((target) => ({
          priority_type: target.type,
          response_time_value: target.responseTime.value,
          alert_before_response_value: target.alertBeforeResponse.value,
          resolve_time_value: target.resolveTime.value,
          alert_before_resolve_value: target.alertBeforeResolve.value,
        }))
      return acc
    }, {})

    const formData = {
      sla: {
        name: businessHours.name,
        description: businessHours.description,
        is_24_hours: Boolean(businessHours.is24Hours),
        start_time: businessHours.startTime || null,
        end_time: businessHours.endTime || null,
      },
      off_days: businessHours.weekOffDays,
      holidays: holidays.map((holiday) => ({
        holiday_date: holiday.holiday_date,
        description: holiday.description,
      })),
      targets: formattedTargets, // Add the formatted targets structure here
    }

    if (formData.sla.is_24_hours) {
      formData.sla.start_time = "00:00:00";
      formData.sla.end_time = "23:59:59";
    }
    console.log('formdata ' + JSON.stringify(formData, null, 4))

    const request = isEditMode
      ? axios.put(`${BASE_URL}/api/sla-settings/${id}`, formData, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authToken}`,
        },
      })
      : axios.post(`${BASE_URL}/api/sla-settings`, formData, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${authToken}`,
        },
      })

    request
      .then(() => {
        alert('SLA configuration saved successfully!')
        navigate('/settings/SLA_config_list') // Redirect to the SLA settings list page
      })
      .catch((error) => {
        console.error('Error saving SLA:', error)
        alert('Failed to save SLA configuration.')
      })
  }

  const handleCancel = () => {
    if (window.confirm('Are you sure you want to discard changes?')) {
      navigate('/settings/SLA_config_list') // Redirect to SLA settings list page
    }
  }

  const addHoliday = () => {
    setHolidays([...holidays, { holiday_date: '', description: '' }])
  }

  const updateHoliday = (index, field, value) => {
    const updatedHolidays = holidays.map((holiday, idx) =>
      idx === index ? { ...holiday, [field]: value } : holiday,
    )
    setHolidays(updatedHolidays)
  }

  const removeHoliday = (index) => {
    setHolidays(holidays.filter((_, idx) => idx !== index))
  }

  const handleRemoveTargetLevel = (level) => {
    const newLevels = { ...levels }
    delete newLevels[level] // Remove the specified level
    setLevels(newLevels) // Update the state
  }

  return (
    <CCard className="mb-3">
      <CCardHeader>
        <h4>{isEditMode ? 'Edit SLA' : 'Create SLA'}</h4>
      </CCardHeader>
      <CCardBody>
        {/* SLA Business Hours Section */}
        <CRow>
          <CCol md="6" className="mb-3">
            <CFormLabel htmlFor="name">
              SLA Name <span style={{ color: 'red' }}>*</span>
            </CFormLabel>
            <CFormInput
              type="text"
              id="name"
              name="name"
              value={businessHours.name}
              onChange={(e) => setBusinessHours({ ...businessHours, name: e.target.value })}
              required
            />
          </CCol>
          <CCol md="6" className="mb-3">
            <CFormLabel htmlFor="description">
              Description <span style={{ color: 'red' }}>*</span>
            </CFormLabel>
            <CFormInput
              type="text"
              id="description"
              name="description"
              value={businessHours.description}
              onChange={(e) => setBusinessHours({ ...businessHours, description: e.target.value })}
              required
            />
          </CCol>
        </CRow>
        <h5 className="mb-3">SLA Business Hours</h5>
        <CForm>
          <CRow className="align-items-center mb-4">
            <CCol xs="2" className="d-flex align-items-center">
              <CFormLabel className="mb-0">24 Hours:</CFormLabel>
            </CCol>
            <CCol xs="1" className="d-flex align-items-center">
              <CFormSwitch
                size="lg"
                style={{ width: '2.25em' }}
                id="is24Hours"
                checked={businessHours.is24Hours}
                onChange={(e) =>
                  setBusinessHours({ ...businessHours, is24Hours: e.target.checked })
                }
              />
            </CCol>

            {!businessHours.is24Hours && (
              <>
                <CCol xs="4" className="d-flex align-items-center">
                  <div className="col-4">
                    <CFormLabel className="me-2 mb-0">Start Time:</CFormLabel>
                  </div>
                  <div className="col-8">
                    <Datetime
                      value={businessHours.startTime}
                      onChange={(e) =>
                        setBusinessHours({ ...businessHours, startTime: extractTime(e?._d) })
                      }
                      timeFormat="HH:mm:ss"
                      dateFormat={false}
                    />
                  </div>
                </CCol>
                <CCol xs="5" className="d-flex align-items-center">
                  <div className="col-4">
                    <CFormLabel className="me-2 mb-0">End Time:</CFormLabel>
                  </div>
                  <div className="col-8">
                    <Datetime
                      value={businessHours.endTime}
                      onChange={(e) =>
                        setBusinessHours({ ...businessHours, endTime: extractTime(e?._d) })
                      }
                      timeFormat="HH:mm:ss"
                      dateFormat={false}
                    />
                  </div>
                </CCol>
              </>
            )}
          </CRow>

          {/* Week Off Days Section */}
          <div className="mb-4">
            <h6>Week Off Days:</h6>

            <CRow>
              <CCol xs="4">
                {['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Holiday'].map((day) => (
                  <CBadge
                    key={day}
                    color={
                      day === 'Holiday'
                        ? holidays.length > 0
                          ? 'danger'
                          : 'success'
                        : businessHours.weekOffDays.includes(day)
                          ? 'danger'
                          : 'success'
                    }
                    className="me-2 mb-2"
                    style={{ cursor: 'pointer' }}
                    onClick={() => {
                      if (day === 'Holiday') {
                        setShowModal(true) // Open the modal when 'Holiday' is clicked
                      } else {
                        const newWeekOffDays = businessHours.weekOffDays.includes(day)
                          ? businessHours.weekOffDays.filter((d) => d !== day)
                          : [...businessHours.weekOffDays, day]
                        setBusinessHours({ ...businessHours, weekOffDays: newWeekOffDays })
                      }
                    }}
                  >
                    {day}
                  </CBadge>
                ))}
              </CCol>

              {/* Holiday Modal */}
              <CModal visible={showModal} onClose={() => setShowModal(false)}>
                <CModalHeader onClose={() => setShowModal(false)}>Holidays</CModalHeader>
                <CModalBody>
                  <CCard className="mb-4">
                    <CCardBody>
                      <CTable>
                        <CTableHead>
                          <CTableRow>
                            <CTableHeaderCell>Date</CTableHeaderCell>
                            <CTableHeaderCell>Description</CTableHeaderCell>
                            <CTableHeaderCell>Actions</CTableHeaderCell>
                          </CTableRow>
                        </CTableHead>
                        <CTableBody>
                          {(holidays.length === 0 ? [{}] : holidays).map((holiday, index) => (
                            <CTableRow key={index}>
                              <CTableDataCell>
                                <CFormInput
                                  type="date"
                                  value={holiday.holiday_date || ''}
                                  onChange={(e) =>
                                    updateHoliday(index, 'holiday_date', e.target.value)
                                  }
                                />
                              </CTableDataCell>
                              <CTableDataCell>
                                <CFormInput
                                  type="text"
                                  value={holiday.description || ''}
                                  onChange={(e) =>
                                    updateHoliday(index, 'description', e.target.value)
                                  }
                                />
                              </CTableDataCell>
                              <CTableDataCell>
                                <CButton
                                  color="danger"
                                  size="sm"
                                  onClick={() => removeHoliday(index)}
                                  disabled={holidays.length === 0 && index === 0}
                                >
                                  Remove
                                </CButton>
                              </CTableDataCell>
                            </CTableRow>
                          ))}
                        </CTableBody>
                      </CTable>
                      <div className="d-flex justify-content-end mt-2">
                        <CButton
                          color="primary"
                          size="sm"
                          className="rounded-circle"
                          style={{ width: '30px', height: '30px' }}
                          onClick={addHoliday}
                        >
                          +
                        </CButton>
                      </div>
                    </CCardBody>
                  </CCard>
                </CModalBody>
                <CModalFooter>
                  <CButton color="secondary" onClick={() => setShowModal(false)}>
                    Close
                  </CButton>
                </CModalFooter>
              </CModal>
            </CRow>
          </div>
        </CForm>

        {/* SLA Targets Section */}
        <h5 className="mb-3 d-flex justify-content-between align-items-center">
          Targets
          <CButton
            color="primary"
            onClick={handleAddTargetLevel}
          // disabled={targetLevels.length >= 4}
          >
            Add Level
          </CButton>
        </h5>

        {Object.keys(levels).map((level) => (
          <div key={level}>
            <h6 className="d-flex justify-content-between align-items-center">
              Level {level}
              {Object.keys(levels).length > 1 &&
                level !== '1' && ( // Check if level is not 1
                  <CButton color="danger" size="sm" onClick={() => handleRemoveTargetLevel(level)}>
                    Remove Level
                  </CButton>
                )}
            </h6>
            <CTable borderless responsive className="align-middle">
              <thead className="fw-bold">
                <tr className="border-bottom">
                  <th style={{ width: '5%' }}></th>
                  <th style={{ width: '15%' }}>Priority Type</th>
                  <th style={{ width: '20%' }}>Response Time</th>
                  <th style={{ width: '20%' }}>Alert Before Response</th>
                  <th style={{ width: '20%' }}>Resolve Time</th>
                  <th style={{ width: '20%' }}>Alert Before Resolve</th>
                </tr>
              </thead>
              <tbody>
                {levels[level].map((target, index) => (
                  <tr key={index}>
                    <td>
                      <CFormSwitch
                        checked={target.enabled}
                        onChange={() => handleLevelSwitchChange(level, index)}
                      />
                    </td>
                    <td>
                      <CFormLabel>{target.type}</CFormLabel>
                    </td>
                    <td>
                      <CFormInput
                        type="number"
                        value={target.responseTime.value}
                        onChange={(e) =>
                          handleLevelInputChange(
                            level,
                            index,
                            'responseTime',
                            'value',
                            e.target.value,
                          )
                        }
                        disabled={!target.enabled}
                      />
                    </td>
                    <td>
                      <CFormInput
                        type="number"
                        value={target.alertBeforeResponse.value}
                        onChange={(e) =>
                          handleLevelInputChange(
                            level,
                            index,
                            'alertBeforeResponse',
                            'value',
                            e.target.value,
                          )
                        }
                        disabled={!target.enabled} // Disable if switch is off
                      />
                    </td>
                    <td>
                      <CFormInput
                        type="number"
                        value={target.resolveTime.value}
                        onChange={(e) =>
                          handleLevelInputChange(
                            level,
                            index,
                            'resolveTime',
                            'value',
                            e.target.value,
                          )
                        }
                        disabled={!target.enabled} // Disable if switch is off
                      />
                    </td>
                    <td>
                      <CFormInput
                        type="number"
                        value={target.alertBeforeResolve.value}
                        onChange={(e) =>
                          handleLevelInputChange(
                            level,
                            index,
                            'alertBeforeResolve',
                            'value',
                            e.target.value,
                          )
                        }
                        disabled={!target.enabled} // Disable if switch is off
                      />
                    </td>
                  </tr>
                ))}
              </tbody>
            </CTable>
          </div>
        ))}

        <CRow>
          <CCol className="text-end">
            <CButton color="success" className="me-2" onClick={handleSave}>
              Save
            </CButton>
            <CButton color="danger" onClick={handleCancel}>
              Cancel
            </CButton>
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>
  )
}

export default SLAConfigurationForm
