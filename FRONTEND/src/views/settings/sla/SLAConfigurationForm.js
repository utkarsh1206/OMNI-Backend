// import React, { useState } from 'react';
// import {
//   CForm,
//   CFormLabel,
//   CFormInput,
//   CRow,
//   CCol,
//   CFormSelect,
//   CFormSwitch,
//   CButton,
//   CCard,
//   CCardBody,
//   CCardHeader,
//   CBadge,
//   CInputGroup,
// } from '@coreui/react';

// const SLAConfigurationForm = () => {
//   const [businessHours, setBusinessHours] = useState({
//     is24Hours: true,
//     startTime: '09:00',
//     endTime: '15:00',
//     weekOffDays: ['Sat', 'Sun', 'Holiday'],
//   });

//   const [targets, setTargets] = useState([
//     {
//       type: 'Critical',
//       responseTime: { value: 20, unit: 'Minute' },
//       alertBeforeResponse: { value: 10, unit: 'Minute' },
//       resolveTime: { value: 1, unit: 'Hour' },
//       alertBeforeResolve: { value: 10, unit: 'Minute' },
//       enabled: true,
//     },
//     {
//       type: 'High',
//       responseTime: { value: 40, unit: 'Minute' },
//       alertBeforeResponse: { value: 10, unit: 'Minute' },
//       resolveTime: { value: 3, unit: 'Hour' },
//       alertBeforeResolve: { value: 1, unit: 'Hour' },
//       enabled: true,
//     },
//     {
//       type: 'Low',
//       responseTime: { value: 24, unit: 'Hour' },
//       alertBeforeResponse: { value: 6, unit: 'Hour' },
//       resolveTime: { value: 48, unit: 'Hour' },
//       alertBeforeResolve: { value: 10, unit: 'Hour' },
//       enabled: true,
//     },
//     {
//       type: 'Medium',
//       responseTime: { value: 5, unit: 'Hour' },
//       alertBeforeResponse: { value: 3, unit: 'Hour' },
//       resolveTime: { value: 12, unit: 'Hour' },
//       alertBeforeResolve: { value: 4, unit: 'Hour' },
//       enabled: true,
//     },
//   ]);

//   const handleSwitchChange = (index) => {
//     const newTargets = [...targets];
//     newTargets[index].enabled = !newTargets[index].enabled;
//     setTargets(newTargets);
//   };

//   const handleInputChange = (index, field, subField, value) => {
//     const newTargets = [...targets];
//     newTargets[index][field][subField] = value;
//     setTargets(newTargets);
//   };

//   const handleSave = () => {
//     console.log('Business Hours:', businessHours);
//     console.log('Targets:', targets);
//     alert('SLA Updated Successfully!');
//   };

//   const handleCancel = () => {
//     alert('Changes Discarded!');
//   };

//   return (
//     <CCard>
//       <CCardHeader>Update SLA</CCardHeader>
//       <CCardBody>
//         {/* SLA Business Hours Section */}
//         <h5>SLA Business Hours</h5>
//         <CForm>
//           <CInputGroup>
//             <CFormLabel>24 Hours:</CFormLabel>
//             <CFormSwitch
//               className="ms-3"
//               shape="pill"
//               color="success"
//               checked={businessHours.is24Hours}
//               onChange={(e) =>
//                 setBusinessHours({ ...businessHours, is24Hours: e.target.checked })
//               }
//             />
//           </CInputGroup>
//           {!businessHours.is24Hours && (
//             <CRow>
//               <CCol>
//                 <CInputGroup>
//                   <CFormLabel>Start Time:</CFormLabel>
//                   <CFormInput
//                     type="time"
//                     value={businessHours.startTime}
//                     onChange={(e) =>
//                       setBusinessHours({ ...businessHours, startTime: e.target.value })
//                     }
//                   />
//                 </CInputGroup>
//               </CCol>
//               <CCol>
//                 <CInputGroup>
//                   <CFormLabel>End Time:</CFormLabel>
//                   <CFormInput
//                     type="time"
//                     value={businessHours.endTime}
//                     onChange={(e) =>
//                       setBusinessHours({ ...businessHours, endTime: e.target.value })
//                     }
//                   />
//                 </CInputGroup>
//               </CCol>
//             </CRow>
//           )}
//           <CInputGroup>
//             <CFormLabel>Week Off Days:</CFormLabel>
//             <div>
//               {['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Holiday'].map((day) => (
//                 <CBadge
//                   key={day}
//                   color={businessHours.weekOffDays.includes(day) ? 'danger' : 'success'}
//                   className="mr-2"
//                   onClick={() => {
//                     const newWeekOffDays = businessHours.weekOffDays.includes(day)
//                       ? businessHours.weekOffDays.filter((d) => d !== day)
//                       : [...businessHours.weekOffDays, day];
//                     setBusinessHours({ ...businessHours, weekOffDays: newWeekOffDays });
//                   }}
//                 >
//                   {day}
//                 </CBadge>
//               ))}
//             </div>
//           </CInputGroup>
//         </CForm>

//         {/* SLA Targets Section */}
//         <h5>Targets</h5>
//         {targets.map((target, index) => (
//           <CRow key={index} className="mb-3">
//             <CCol md="1">
//               <CFormSwitch
//                 shape="pill"
//                 color="success"
//                 checked={target.enabled}
//                 onChange={() => handleSwitchChange(index)}
//               />
//             </CCol>
//             <CCol md="2">
//               <CFormLabel>{target.type}</CFormLabel>
//             </CCol>
//             <CCol md="2">
//               <CFormInput
//                 type="number"
//                 value={target.responseTime.value}
//                 onChange={(e) =>
//                   handleInputChange(index, 'responseTime', 'value', e.target.value)
//                 }
//               />
//               <CFormSelect
//                 value={target.responseTime.unit}
//                 onChange={(e) =>
//                   handleInputChange(index, 'responseTime', 'unit', e.target.value)
//                 }
//               >
//                 <option>Minute</option>
//                 <option>Hour</option>
//               </CFormSelect>
//             </CCol>
//             <CCol md="2">
//               <CFormInput
//                 type="number"
//                 value={target.alertBeforeResponse.value}
//                 onChange={(e) =>
//                   handleInputChange(index, 'alertBeforeResponse', 'value', e.target.value)
//                 }
//               />
//               <CFormSelect
//                 value={target.alertBeforeResponse.unit}
//                 onChange={(e) =>
//                   handleInputChange(index, 'alertBeforeResponse', 'unit', e.target.value)
//                 }
//               >
//                 <option>Minute</option>
//                 <option>Hour</option>
//               </CFormSelect>
//             </CCol>
//             <CCol md="2">
//               <CFormInput
//                 type="number"
//                 value={target.resolveTime.value}
//                 onChange={(e) =>
//                   handleInputChange(index, 'resolveTime', 'value', e.target.value)
//                 }
//               />
//               <CFormSelect
//                 value={target.resolveTime.unit}
//                 onChange={(e) =>
//                   handleInputChange(index, 'resolveTime', 'unit', e.target.value)
//                 }
//               >
//                 <option>Minute</option>
//                 <option>Hour</option>
//               </CFormSelect>
//             </CCol>
//             <CCol md="2">
//               <CFormInput
//                 type="number"
//                 value={target.alertBeforeResolve.value}
//                 onChange={(e) =>
//                   handleInputChange(index, 'alertBeforeResolve', 'value', e.target.value)
//                 }
//               />
//               <CFormSelect
//                 value={target.alertBeforeResolve.unit}
//                 onChange={(e) =>
//                   handleInputChange(index, 'alertBeforeResolve', 'unit', e.target.value)
//                 }
//               >
//                 <option>Minute</option>
//                 <option>Hour</option>
//               </CFormSelect>
//             </CCol>
//           </CRow>
//         ))}

//         {/* Save and Cancel Buttons */}
//         <CRow>
//           <CCol>
//             <CButton color="success" onClick={handleSave}>
//               Save
//             </CButton>
//             <CButton color="danger" className="ml-2" onClick={handleCancel}>
//               Cancel
//             </CButton>
//           </CCol>
//         </CRow>
//       </CCardBody>
//     </CCard>
//   );
// };

// export default SLAConfigurationForm;



import React, { useState } from 'react';
import {
  CForm,
  CFormLabel,
  CFormInput,
  CRow,
  CCol,
  CFormSelect,
  CFormSwitch,
  CButton,
  CCard,
  CCardBody,
  CCardHeader,
  CBadge,
  CInputGroup,
  CInputGroupText,
} from '@coreui/react';

const SLAConfigurationForm = () => {
  const [businessHours, setBusinessHours] = useState({
    is24Hours: true,
    startTime: '09:00',
    endTime: '15:00',
    weekOffDays: ['Sat', 'Sun', 'Holiday'],
  });

  const [targets, setTargets] = useState([
    {
      type: 'Critical',
      responseTime: { value: 20, unit: 'Minute' },
      alertBeforeResponse: { value: 10, unit: 'Minute' },
      resolveTime: { value: 1, unit: 'Hour' },
      alertBeforeResolve: { value: 10, unit: 'Minute' },
      enabled: true,
    },
    {
      type: 'High',
      responseTime: { value: 40, unit: 'Minute' },
      alertBeforeResponse: { value: 10, unit: 'Minute' },
      resolveTime: { value: 3, unit: 'Hour' },
      alertBeforeResolve: { value: 1, unit: 'Hour' },
      enabled: true,
    },
    {
      type: 'Low',
      responseTime: { value: 24, unit: 'Hour' },
      alertBeforeResponse: { value: 6, unit: 'Hour' },
      resolveTime: { value: 48, unit: 'Hour' },
      alertBeforeResolve: { value: 10, unit: 'Hour' },
      enabled: true,
    },
    {
      type: 'Medium',
      responseTime: { value: 5, unit: 'Hour' },
      alertBeforeResponse: { value: 3, unit: 'Hour' },
      resolveTime: { value: 12, unit: 'Hour' },
      alertBeforeResolve: { value: 4, unit: 'Hour' },
      enabled: true,
    },
  ]);

  const handleSwitchChange = (index) => {
    const newTargets = [...targets];
    newTargets[index].enabled = !newTargets[index].enabled;
    setTargets(newTargets);
  };

  const handleInputChange = (index, field, subField, value) => {
    const newTargets = [...targets];
    newTargets[index][field][subField] = value;
    setTargets(newTargets);
  };

  const handleSave = () => {
    console.log('Business Hours:', businessHours);
    console.log('Targets:', targets);
    alert('SLA Updated Successfully!');
  };

  const handleCancel = () => {
    alert('Changes Discarded!');
  };

  return (
    <CCard>
      <CCardHeader>Update SLA</CCardHeader>
      <CCardBody>
        {/* SLA Business Hours Section */}
        <h5>SLA Business Hours</h5>
        <CForm>
          <CRow className="mb-3">
            <CCol xs="3">
              <CFormLabel>24 Hours:</CFormLabel>
            </CCol>
            <CCol xs="9">
              <CFormSwitch
                id="is24Hours"
                label=""
                checked={businessHours.is24Hours}
                onChange={(e) =>
                  setBusinessHours({ ...businessHours, is24Hours: e.target.checked })
                }
              />
            </CCol>
          </CRow>
          {!businessHours.is24Hours && (
            <CRow className="mb-3">
              <CCol>
                <CFormLabel>Start Time:</CFormLabel>
                <CFormInput
                  type="time"
                  value={businessHours.startTime}
                  onChange={(e) =>
                    setBusinessHours({ ...businessHours, startTime: e.target.value })
                  }
                />
              </CCol>
              <CCol>
                <CFormLabel>End Time:</CFormLabel>
                <CFormInput
                  type="time"
                  value={businessHours.endTime}
                  onChange={(e) =>
                    setBusinessHours({ ...businessHours, endTime: e.target.value })
                  }
                />
              </CCol>
            </CRow>
          )}
          <CFormLabel className='me-3'>Week Off Days:</CFormLabel>
          {/* <div className="mb-3"> */}
            {['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Holiday'].map((day) => (
              <CBadge
                key={day}
                color={businessHours.weekOffDays.includes(day) ? 'danger' : 'success'}
                className="me-2"
                style={{ cursor: 'pointer' }}
                onClick={() => {
                  const newWeekOffDays = businessHours.weekOffDays.includes(day)
                    ? businessHours.weekOffDays.filter((d) => d !== day)
                    : [...businessHours.weekOffDays, day];
                  setBusinessHours({ ...businessHours, weekOffDays: newWeekOffDays });
                }}
              >
                {day}
              </CBadge>
            ))}
          {/* </div> */}
        </CForm>

        {/* SLA Targets Section */}
        <h5>Targets</h5>
        <CRow className='align-items-center mb-3'>
          <CCol xs="1"></CCol>
          <CCol xs="2">Priority Type</CCol>
          <CCol xs="2">Response Time</CCol>
          <CCol xs="2">Alert Before Response</CCol>
          <CCol xs="2">Resolve Time</CCol>
          <CCol xs="2">Alert Before Resolve</CCol>
        </CRow>
        {targets.map((target, index) => (
          <CRow key={index} className="align-items-center mb-3">
            <CCol xs="1">
              <CFormSwitch
                checked={target.enabled}
                onChange={() => handleSwitchChange(index)}
              />
            </CCol>
            <CCol xs="2">
              <CFormLabel>{target.type}</CFormLabel>
            </CCol>
            <CCol xs="2">
              <CInputGroup>
                <CFormInput
                  type="number"
                  value={target.responseTime.value}
                  onChange={(e) =>
                    handleInputChange(index, 'responseTime', 'value', e.target.value)
                  }
                />
                <CFormSelect
                  value={target.responseTime.unit}
                  onChange={(e) =>
                    handleInputChange(index, 'responseTime', 'unit', e.target.value)
                  }
                >
                  <option>Minute</option>
                  <option>Hour</option>
                </CFormSelect>
              </CInputGroup>
            </CCol>
            <CCol xs="2">
              <CInputGroup>
                <CFormInput
                  type="number"
                  value={target.alertBeforeResponse.value}
                  onChange={(e) =>
                    handleInputChange(index, 'alertBeforeResponse', 'value', e.target.value)
                  }
                />
                <CFormSelect
                  value={target.alertBeforeResponse.unit}
                  onChange={(e) =>
                    handleInputChange(index, 'alertBeforeResponse', 'unit', e.target.value)
                  }
                >
                  <option>Minute</option>
                  <option>Hour</option>
                </CFormSelect>
              </CInputGroup>
            </CCol>
            <CCol xs="2">
              <CInputGroup>
                <CFormInput
                  type="number"
                  value={target.resolveTime.value}
                  onChange={(e) =>
                    handleInputChange(index, 'resolveTime', 'value', e.target.value)
                  }
                />
                <CFormSelect
                  value={target.resolveTime.unit}
                  onChange={(e) =>
                    handleInputChange(index, 'resolveTime', 'unit', e.target.value)
                  }
                >
                  <option>Minute</option>
                  <option>Hour</option>
                </CFormSelect>
              </CInputGroup>
            </CCol>
            <CCol xs="2">
              <CInputGroup>
                <CFormInput
                  type="number"
                  value={target.alertBeforeResolve.value}
                  onChange={(e) =>
                    handleInputChange(index, 'alertBeforeResolve', 'value', e.target.value)
                  }
                />
                <CFormSelect
                  value={target.alertBeforeResolve.unit}
                  onChange={(e) =>
                    handleInputChange(index, 'alertBeforeResolve', 'unit', e.target.value)
                  }
                >
                  <option>Minute</option>
                  <option>Hour</option>
                </CFormSelect>
              </CInputGroup>
            </CCol>
          </CRow>
        ))}

        {/* Save and Cancel Buttons */}
        <CRow>
          <CCol>
            <CButton color="success" onClick={handleSave}>
              Save
            </CButton>
            <CButton color="danger" className="ms-2" onClick={handleCancel}>
              Cancel
            </CButton>
          </CCol>
        </CRow>
      </CCardBody>
    </CCard>
  );
};

export default SLAConfigurationForm;
