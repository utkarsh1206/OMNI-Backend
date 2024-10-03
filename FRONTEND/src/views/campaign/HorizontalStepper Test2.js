// import * as React from 'react';
// import { Stepper, Step, StepLabel, Button, Typography, Box, StepButton } from '@mui/material';
// import Step1 from './createCampaign/Step1';
// import Step2 from './createCampaign/Step2';

// const steps = ['Campaign Details', 'Prompt Files', 'Callback Configuration', 'Call Disposition Configuration', 'Inbound Group Configuration for Transfer', 'Minimum Hoper Level', 'Dial Method', 'Call Route on Hangup'];

// // Array of step indices that are optional (zero-based index)
// const optionalSteps = [1, 3, 5];

// export default function HorizontalNonLinearStepper() {
//     const [activeStep, setActiveStep] = React.useState(0);
//     const [completed, setCompleted] = React.useState({});

//     const [skipped, setSkipped] = useState(new Set());
//     const [formData, setFormData] = useState({
//         campID: '',
//         campName: '',
//         msc_on_hld: '',
//         welcomeMsg: '',
//         // Initialize states for other steps if needed
//     });

//     const isStepOptional = (step, valuesToCheck) => {
//         return valuesToCheck.includes(step);
//     };

//     const isStepSkipped = (step) => {
//         return skipped.has(step);
//     };

//     const totalSteps = () => {
//         return steps.length;
//     };

//     const completedSteps = () => {
//         return Object.keys(completed).length;
//     };

//     const isLastStep = () => {
//         return activeStep === totalSteps() - 1;
//     };

//     const allStepsCompleted = () => {
//         return completedSteps() === totalSteps();
//     };

//     const handleNext = () => {
//         const newActiveStep =
//             isLastStep() && !allStepsCompleted()
//                 ? // It's the last step, but not all steps have been completed,
//                 // find the first step that has been completed
//                 steps.findIndex((step, i) => !(i in completed))
//                 : activeStep + 1;
//         setActiveStep(newActiveStep);
//     };

//     const handleBack = () => {
//         setActiveStep((prevActiveStep) => prevActiveStep - 1);
//     };

//     const handleStep = (step) => () => {
//         setActiveStep(step);
//     };

//     const handleComplete = () => {
//         const newCompleted = completed;
//         newCompleted[activeStep] = true;
//         setCompleted(newCompleted);
//         handleNext();
//     };

//     const handleReset = () => {
//         setActiveStep(0);
//         setCompleted({});

//         setFormData({
//             campID: '',
//             campName: '',
//             msc_on_hld: '',
//             welcomeMsg: '',
//             // Reset states for other steps if needed
//         });
//     };

//     const handleFinish = () => {
//         console.log('All data submitted:', formData);
//         // Perform submission logic here, e.g., send data to an API
//     };

//     return (
//         <Box sx={{ width: '100%' }}>
//             <Stepper nonLinear activeStep={activeStep}>
//                 {steps.map((label, index) => (
//                     <Step key={label} completed={completed[index]}>
//                         <StepButton color="inherit" onClick={handleStep(index)}>
//                             {label}
//                         </StepButton>
//                     </Step>
//                 ))}
//             </Stepper>
//             <div>
//                 {allStepsCompleted() ? (
//                     <React.Fragment>
//                         <Typography sx={{ mt: 2, mb: 1 }}>
//                             All steps completed - you&apos;re finished
//                         </Typography>
//                         <Box sx={{ display: 'flex', flexDirection: 'row', pt: 2 }}>
//                             <Box sx={{ flex: '1 1 auto' }} />
//                             <Button onClick={handleReset}>Reset</Button>
//                         </Box>
//                     </React.Fragment>
//                 ) : (
//                     <React.Fragment>
//                         {/* <Typography sx={{ mt: 2, mb: 1, py: 1 }}>
//               Step {activeStep + 1}
//             </Typography> */}

//                         {/* ------------------------------------ */}
//                         {/* ------------------------------------ */}
//                         {(() => {
//                             switch (activeStep) {
//                                 case 0:
//                                     // return <Step1 formData={formData.step1} setFormData={(data) => setFormData(prev => ({ ...prev, step1: data }))} />
//                                     // return <Step1 formData={formData} setFormData={setFormData} />;
//                                     return <Step1 />;
//                                 case 1:
//                                     // return <Step2 formData={formData.step2} setFormData={(data) => setFormData(prev => ({ ...prev, step2: data }))} />
//                                     // return <Step2 formData={formData} setFormData={setFormData} />;
//                                     return <Step2 />;
//                                 case 2:
//                                     // Code for step 
//                                     break;
//                                 case 3:
//                                     // Code for step 3
//                                     break;
//                                 case 4:
//                                     // Code for step 4
//                                     break;
//                                 case 5:
//                                     // Code for step 5
//                                     break;
//                                 case 6:
//                                     // Code for step 6
//                                     break;
//                                 case 7:
//                                     // Code for step 7
//                                     break;
//                                 default:
//                                     // Code for any other step
//                                     return null;
//                             }
//                         })()}
//                         {/* ------------------------------------ */}
//                         {/* ------------------------------------ */}

//                         <Box sx={{ display: 'flex', flexDirection: 'row', pt: 2 }}>
//                             <Button
//                                 color="inherit"
//                                 disabled={activeStep === 0}
//                                 onClick={handleBack}
//                                 sx={{ mr: 1 }}
//                             >
//                                 Back
//                             </Button>
//                             <Box sx={{ flex: '1 1 auto' }} />
//                             <Button onClick={handleNext} sx={{ mr: 1 }}>
//                                 Next
//                             </Button>
//                             {activeStep !== steps.length &&
//                                 (completed[activeStep] ? (
//                                     <Typography variant="caption" sx={{ display: 'inline-block' }}>
//                                         Step {activeStep + 1} already completed
//                                     </Typography>
//                                 ) : (
//                                     <Button onClick={handleComplete}>
//                                         {completedSteps() === totalSteps() - 1
//                                             ? 'Finish'
//                                             : 'Complete Step'}
//                                     </Button>
//                                 ))}
//                         </Box>
//                     </React.Fragment>
//                 )}
//             </div>
//         </Box>
//     );
// }
//-------------------------------------------------^^^^^1

//------------------------------------------------------2
// import * as React from 'react';
// import { Stepper, Step, StepLabel, Button, Typography, Box, StepButton } from '@mui/material';
// import Step1 from './createCampaign/Step1';
// import Step2 from './createCampaign/Step2';

// const steps = ['Campaign Details', 'Prompt Files', 'Callback Configuration', 'Call Disposition Configuration', 'Inbound Group Configuration for Transfer', 'Minimum Hoper Level', 'Dial Method', 'Call Route on Hangup'];

// // Array of step indices that are optional (zero-based index)
// const optionalSteps = [1, 3, 5];

// export default function HorizontalNonLinearStepper() {
//     const [activeStep, setActiveStep] = React.useState(0);
//     const [completed, setCompleted] = React.useState({});

//     const [skipped, setSkipped] = React.useState(new Set());
//     const [formData, setFormData] = React.useState({
//         campID: '',
//         campName: '',
//         msc_on_hld: '',
//         welcomeMsg: '',
//         // Initialize states for other steps if needed
//     });

//     const isStepOptional = (step) => {
//         return optionalSteps.includes(step);
//     };

//     const isStepSkipped = (step) => {
//         return skipped.has(step);
//     };

//     const totalSteps = () => {
//         return steps.length;
//     };

//     const completedSteps = () => {
//         return Object.keys(completed).length;
//     };

//     const isLastStep = () => {
//         return activeStep === totalSteps() - 1;
//     };

//     const allStepsCompleted = () => {
//         return completedSteps() === totalSteps() - skipped.size;
//     };

//     const handleNext = () => {
//         let newSkipped = skipped;
//         let newActiveStep = activeStep + 1;

//         if (isStepSkipped(newActiveStep)) {
//             newSkipped = new Set(newSkipped.values());
//             newSkipped.delete(newActiveStep);
//         }

//         while (isStepOptional(newActiveStep) && !(newActiveStep in completed)) {
//             newActiveStep += 1;
//         }

//         setActiveStep(newActiveStep);
//         setSkipped(newSkipped);
//     };

//     const handleBack = () => {
//         setActiveStep((prevActiveStep) => prevActiveStep - 1);
//     };

//     const handleStep = (step) => () => {
//         setActiveStep(step);
//     };

//     const handleComplete = () => {
//         const newCompleted = { ...completed };
//         newCompleted[activeStep] = true;
//         setCompleted(newCompleted);
//         handleNext();
//     };

//     const handleSkip = () => {
//         if (!isStepOptional(activeStep)) {
//             // You probably want to do some error handling here
//             throw new Error("You can't skip a step that isn't optional.");
//         }

//         const newSkipped = new Set(skipped.values());
//         newSkipped.add(activeStep);
//         setSkipped(newSkipped);
//         handleNext();
//     };

//     const handleReset = () => {
//         setActiveStep(0);
//         setCompleted({});
//         setSkipped(new Set());

//         setFormData({
//             campID: '',
//             campName: '',
//             msc_on_hld: '',
//             welcomeMsg: '',
//             // Reset states for other steps if needed
//         });
//     };

//     const handleFinish = () => {
//         console.log('All data submitted:', formData);
//         // Perform submission logic here, e.g., send data to an API
//     };

//     return (
//         <Box sx={{ width: '100%' }}>
//             <Stepper nonLinear activeStep={activeStep}>
//                 {steps.map((label, index) => (
//                     <Step key={label} completed={completed[index]}>
//                         <StepButton color="inherit" onClick={handleStep(index)}>
//                             {label}
//                         </StepButton>
//                     </Step>
//                 ))}
//             </Stepper>
//             <div>
//                 {allStepsCompleted() ? (
//                     <React.Fragment>
//                         <Typography sx={{ mt: 2, mb: 1 }}>
//                             All steps completed - you&apos;re finished
//                         </Typography>
//                         <Box sx={{ display: 'flex', flexDirection: 'row', pt: 2 }}>
//                             <Box sx={{ flex: '1 1 auto' }} />
//                             <Button onClick={handleReset}>Reset</Button>
//                         </Box>
//                     </React.Fragment>
//                 ) : (
//                     <React.Fragment>
//                         {/* <Typography sx={{ mt: 2, mb: 1, py: 1 }}>
//               Step {activeStep + 1}
//             </Typography> */}

//                         {/* ------------------------------------ */}
//                         {/* ------------------------------------ */}
//                         {(() => {
//                             switch (activeStep) {
//                                 case 0:
//                                     return <Step1 />;
//                                 case 1:
//                                     return <Step2 />;
//                                 case 2:
//                                     // Code for step 
//                                     break;
//                                 case 3:
//                                     // Code for step 3
//                                     break;
//                                 case 4:
//                                     // Code for step 4
//                                     break;
//                                 case 5:
//                                     // Code for step 5
//                                     break;
//                                 case 6:
//                                     // Code for step 6
//                                     break;
//                                 case 7:
//                                     // Code for step 7
//                                     break;
//                                 default:
//                                     // Code for any other step
//                                     return null;
//                             }
//                         })()}
//                         {/* ------------------------------------ */}
//                         {/* ------------------------------------ */}

//                         <Box sx={{ display: 'flex', flexDirection: 'row', pt: 2 }}>
//                             <Button
//                                 color="inherit"
//                                 disabled={activeStep === 0}
//                                 onClick={handleBack}
//                                 sx={{ mr: 1 }}
//                             >
//                                 Back
//                             </Button>
//                             <Box sx={{ flex: '1 1 auto' }} />
//                             <Button onClick={handleNext} sx={{ mr: 1 }}>
//                                 Next
//                             </Button>
//                             {isStepOptional(activeStep) && (
//                                 <Button onClick={handleSkip} sx={{ mr: 1 }}>
//                                     Skip
//                                 </Button>
//                             )}
//                             {activeStep !== steps.length &&
//                                 (completed[activeStep] ? (
//                                     <Typography variant="caption" sx={{ display: 'inline-block' }}>
//                                         Step {activeStep + 1} already completed
//                                     </Typography>
//                                 ) : (
//                                     <Button onClick={handleComplete}>
//                                         {completedSteps() === totalSteps() - skipped.size - 1
//                                             ? 'Finish'
//                                             : 'Complete Step'}
//                                     </Button>
//                                 ))}
//                         </Box>
//                     </React.Fragment>
//                 )}
//             </div>
//         </Box>
//     );
// }
//----------------------------------------------^^^^^2

//---------------------------------------------------3
import React, { useState } from 'react';
import {
  Box,
  Button,
  Step,
  StepButton,
  Stepper,
  Typography
} from '@mui/material';

const steps = [
  'Step 1',
  'Step 2 (Optional)',
  'Step 3',
  'Step 4 (Optional)',
  'Step 5',
  'Step 6',
  'Step 7 (Optional)',
  'Step 8'
];

const optionalSteps = [1, 3, 6];

function HorizontalNonLinearStepper() {
  const [activeStep, setActiveStep] = useState(0);
  const [completed, setCompleted] = useState({});

  const totalSteps = () => {
    return steps.length;
  };

  const isStepOptional = (step) => {
    return optionalSteps.includes(step);
  };

  const handleNext = () => {
    const newActiveStep = activeStep + 1;
    setActiveStep(newActiveStep);
  };

  const handleBack = () => {
    const newActiveStep = activeStep - 1;
    setActiveStep(newActiveStep);
  };

  const handleSkip = () => {
    if (!isStepOptional(activeStep)) {
      throw new Error("You can't skip a step that isn't optional.");
    }
    const newActiveStep = activeStep + 1;
    setActiveStep(newActiveStep);
  };

  const handleStep = (step) => () => {
    setActiveStep(step);
  };

  const handleComplete = () => {
    const newCompleted = completed;
    newCompleted[activeStep] = true;
    setCompleted(newCompleted);
    handleNext();
  };

  const isStepComplete = (step) => {
    return completed[step];
  };

  const completedSteps = () => {
    return Object.keys(completed).length;
  };

  const allStepsCompleted = () => {
    return completedSteps() === totalSteps();
  };

  return (
    <Box sx={{ width: '100%' }}>
      <Stepper nonLinear activeStep={activeStep}>
        {steps.map((label, index) => (
          <Step key={label} completed={isStepComplete(index)}>
            <StepButton color="inherit" onClick={handleStep(index)}>
              {label}
            </StepButton>
          </Step>
        ))}
      </Stepper>
      <div>
        {allStepsCompleted() ? (
          <div>
            <Typography sx={{ mt: 2, mb: 1 }}>
              All steps completed - you&apos;re finished
            </Typography>
            <Button onClick={() => setActiveStep(0)}>Reset</Button>
          </div>
        ) : (
          <div>
            <Typography sx={{ mt: 2, mb: 1 }}>
              Step {activeStep + 1}
            </Typography>
            <Box sx={{ display: 'flex', flexDirection: 'row', pt: 2 }}>
              <Button
                color="inherit"
                disabled={activeStep === 0}
                onClick={handleBack}
                sx={{ mr: 1 }}
              >
                Back
              </Button>
              {isStepOptional(activeStep) && (
                <Button color="inherit" onClick={handleSkip} sx={{ mr: 1 }}>
                  Skip
                </Button>
              )}
              <Button onClick={handleComplete}>
                {completedSteps() === totalSteps() - 1 ? 'Finish' : 'Complete Step'}
              </Button>
            </Box>
          </div>
        )}
      </div>
    </Box>
  );
}

export default HorizontalNonLinearStepper;
//------------------------------------------------^^^^^3

