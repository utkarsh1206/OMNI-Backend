// import * as React from 'react';
// import Box from '@mui/material/Box';
// import Stepper from '@mui/material/Stepper';
// import Step from '@mui/material/Step';
// import StepLabel from '@mui/material/StepLabel';
// import Button from '@mui/material/Button';
// import Typography from '@mui/material/Typography';
// import { StepButton } from '@mui/material';

// const steps = ['Campaign Details', 'Prompt Files', 'Callback Configuration', 'Call Disposition Configuration', 'Inbound Group Configuration for Transfer', 'Minimum Hoper Level', 'Dial Method', 'Call Route on Hangup'];

// const optionalSteps = [1, 3, 5];

// export default function HorizontalLinearStepper3() {
//     const [activeStep, setActiveStep] = React.useState(0);
//     const [skipped, setSkipped] = React.useState(new Set());
//     const [completed, setCompleted] = React.useState({});

//     const totalSteps = () => {
//         return steps.length;
//     };

//     const completedSteps = () => {
//         return Object.keys(completed).length;
//     };

//     const isStepOptional = (step, valuesToCheck) => {
//         return valuesToCheck.includes(step);
//     };

//     const isStepSkipped = (step) => {
//         return skipped.has(step);
//     };

//     const handleNext = () => {
//         let newSkipped = skipped;
//         if (isStepSkipped(activeStep)) {
//             newSkipped = new Set(newSkipped.values());
//             newSkipped.delete(activeStep);
//         }

//         setActiveStep((prevActiveStep) => prevActiveStep + 1);
//         setSkipped(newSkipped);
//     };

//     const handleBack = () => {
//         setActiveStep((prevActiveStep) => prevActiveStep - 1);
//     };

//     const handleSkip = () => {
//         if (!isStepOptional(activeStep, optionalSteps)) {
//             // You probably want to guard against something like this,
//             // it should never occur unless someone's actively trying to break something.
//             throw new Error("You can't skip a step that isn't optional.");
//         }

//         setActiveStep((prevActiveStep) => prevActiveStep + 1);
//         setSkipped((prevSkipped) => {
//             const newSkipped = new Set(prevSkipped.values());
//             newSkipped.add(activeStep);
//             return newSkipped;
//         });
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
//     };

//     const handleFinish = () => {
//         // console.log('All data submitted:', formData);
//         console.log('All data submitted');
//         // Perform submission logic here, e.g., send data to an API
//     };

//     const handleStep = (step) => () => {
//         setActiveStep(step);
//     };

//     return (
//         <Box sx={{ width: '100%' }}>
//             <Stepper nonLinear alternativeLabel activeStep={activeStep}>
//                 {steps.map((label, index) => {
//                     const stepProps = {};
//                     const labelProps = {};
//                     if (isStepOptional(index, optionalSteps)) {
//                         labelProps.optional = (
//                             <Typography variant="caption">Optional</Typography>
//                         );
//                     }
//                     if (isStepSkipped(index)) {
//                         stepProps.completed = false;
//                     }
//                     return (
//                         <Step key={label} {...stepProps}>
//                             <StepButton {...labelProps} color="inherit" onClick={handleStep(index)}>{label}</StepButton>
//                         </Step>
//                     );
//                 })}
//             </Stepper>
//             {activeStep === steps.length ? (
//                 <React.Fragment>
//                     <Typography sx={{ mt: 2, mb: 1 }}>
//                         All steps completed - you&apos;re finished
//                     </Typography>
//                     <Box sx={{ display: 'flex', flexDirection: 'row', pt: 2 }}>
//                         <Box sx={{ flex: '1 1 auto' }} />
//                         <Button onClick={handleReset}>Reset</Button>
//                     </Box>
//                 </React.Fragment>
//             ) : (
//                 <React.Fragment>
//                     <Typography sx={{ mt: 2, mb: 1 }}>Step {activeStep + 1}</Typography>
//                     <Box sx={{ display: 'flex', flexDirection: 'row', pt: 2 }}>
//                         <Button
//                             color="inherit"
//                             disabled={activeStep === 0}
//                             onClick={handleBack}
//                             sx={{ mr: 1 }}
//                         >
//                             Back
//                         </Button>
//                         <Box sx={{ flex: '1 1 auto' }} />
//                         {isStepOptional(activeStep, optionalSteps) && (
//                             <Button color="inherit" onClick={handleSkip} sx={{ mr: 1 }}>
//                                 Skip
//                             </Button>
//                         )}

//                         {/* <Button onClick={handleNext}>
//               {activeStep === steps.length - 1 ? 'Finish' : 'Next'}
//             </Button> */}

//                         <Button onClick={handleNext} sx={{ mr: 1 }}>
//                             Next
//                         </Button>
//                         {activeStep !== steps.length &&
//                             (completed[activeStep] ? (
//                                 <Typography variant="caption" sx={{ display: 'inline-block' }}>
//                                     Step {activeStep + 1} already completed
//                                 </Typography>
//                             ) : (
//                                 <Button onClick={handleComplete}>
//                                     {completedSteps() === totalSteps() - 1
//                                         ? 'Finish'
//                                         : 'Complete Step'}
//                                 </Button>
//                             ))}

//                     </Box>
//                 </React.Fragment>
//             )}
//         </Box>
//     );
// }


// ---------------------------------------------------------

// import * as React from 'react';
// import { Stepper, Step, StepLabel, Button, Box, Typography } from '@mui/material';

// const steps = [
//   'Campaign Details',
//   'Prompt Files',
//   'Callback Configuration',
//   'Call Disposition Configuration',
//   'Inbound Group Configuration for Transfer',
//   'Minimum Hoper Level',
//   'Dial Method',
//   'Call Route on Hangup',
// ];

// const optionalSteps = [1, 3, 5]; // Indices of steps that are optional

// export default function HorizontalNonLinearStepper3() {
//   const [activeStep, setActiveStep] = React.useState(0);
//   const [skipped, setSkipped] = React.useState(new Set());

//   const isStepOptional = (step) => optionalSteps.includes(step);

//   const handleNext = () => {
//     let newActiveStep = activeStep + 1;
//     while (skipped.has(newActiveStep)) {
//       newActiveStep++;
//     }
//     setActiveStep(newActiveStep);
//   };

//   const handleBack = () => {
//     setActiveStep((prevActiveStep) => prevActiveStep - 1);
//   };

//   const handleSkip = () => {
//     if (!isStepOptional(activeStep)) {
//       throw new Error("You can't skip a step that isn't optional.");
//     }
//     setActiveStep((prevActiveStep) => prevActiveStep + 1);
//     setSkipped((prevSkipped) => {
//       const newSkipped = new Set(prevSkipped);
//       newSkipped.add(activeStep);
//       return newSkipped;
//     });
//   };

//   const handleReset = () => {
//     setActiveStep(0);
//     setSkipped(new Set());
//   };

//   return (
//     <Box sx={{ width: '100%' }}>
//       <Stepper activeStep={activeStep} alternativeLabel>
//         {steps.map((label, index) => {
//           const stepProps = {};
//           const labelProps = {};
//           if (isStepOptional(index)) {
//             labelProps.optional = (
//               <Typography variant="caption" color="text.secondary">
//                 Optional
//               </Typography>
//             );
//           }
//           if (skipped.has(index)) {
//             stepProps.completed = false;
//           }
//           return (
//             <Step key={label} {...stepProps}>
//               <StepLabel {...labelProps}>{label}</StepLabel>
//             </Step>
//           );
//         })}
//       </Stepper>
//       <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 2 }}>
//         <Button
//           disabled={activeStep === 0}
//           onClick={handleBack}
//           sx={{ mr: 1 }}
//         >
//           Back
//         </Button>
//         <div>
//           {isStepOptional(activeStep) && (
//             <Button onClick={handleSkip} sx={{ mr: 1 }}>
//               Skip
//             </Button>
//           )}
//           <Button onClick={handleNext}>
//             {activeStep === steps.length - 1 ? 'Finish' : 'Next'}
//           </Button>
//         </div>
//       </Box>
//       {activeStep === steps.length && (
//         <Box sx={{ mt: 2 }}>
//           <Typography>All steps completed - you're finished</Typography>
//           <Button onClick={handleReset} sx={{ mt: 1 }}>
//             Reset
//           </Button>
//         </Box>
//       )}
//     </Box>
//   );
// }


// ---------------------------------------------------------

// import * as React from 'react';
// import { Stepper, Step, StepLabel, Button, Box, Typography } from '@mui/material';

// const steps = [
//   'Campaign Details',
//   'Prompt Files',
//   'Callback Configuration',
//   'Call Disposition Configuration',
//   'Inbound Group Configuration for Transfer',
//   'Minimum Hoper Level',
//   'Dial Method',
//   'Call Route on Hangup',
// ];

// const optionalSteps = [1, 3, 5]; // Indices of steps that are optional

// export default function HorizontalLinearStepper3() {
//   const [activeStep, setActiveStep] = React.useState(0);
//   const [skipped, setSkipped] = React.useState(new Set());

//   const isStepOptional = (step) => optionalSteps.includes(step);

//   const handleStepClick = (step) => () => {
//     setActiveStep(step);
//   };

//   const handleNext = () => {
//     let newActiveStep = activeStep + 1;
//     while (skipped.has(newActiveStep)) {
//       newActiveStep++;
//     }
//     setActiveStep(newActiveStep);
//   };

//   const handleBack = () => {
//     setActiveStep((prevActiveStep) => prevActiveStep - 1);
//   };

//   const handleSkip = () => {
//     if (!isStepOptional(activeStep)) {
//       throw new Error("You can't skip a step that isn't optional.");
//     }
//     setActiveStep((prevActiveStep) => prevActiveStep + 1);
//     setSkipped((prevSkipped) => {
//       const newSkipped = new Set(prevSkipped);
//       newSkipped.add(activeStep);
//       return newSkipped;
//     });
//   };

//   const handleReset = () => {
//     setActiveStep(0);
//     setSkipped(new Set());
//   };

//   return (
//     <Box sx={{ width: '100%' }}>
//       <Stepper activeStep={activeStep} alternativeLabel>
//         {steps.map((label, index) => {
//           const stepProps = {};
//           const labelProps = {};
//           const isCurrentStep = activeStep === index;

//           if (isStepOptional(index)) {
//             labelProps.optional = (
//               <Typography variant="caption" color="text.secondary">
//                 Optional
//               </Typography>
//             );
//           }

//           if (skipped.has(index)) {
//             stepProps.completed = false;
//           }

//           return (
//             <Step key={label} {...stepProps}>
//               <StepLabel
//                 onClick={handleStepClick(index)}
//                 completed={stepProps.completed}
//                 optional={labelProps.optional}
//                 sx={{
//                   cursor: 'pointer',
//                   '&:hover': {
//                     color: isCurrentStep ? 'inherit' : '#1976d2',
//                   },
//                 }}
//               >
//                 {label}
//               </StepLabel>
//             </Step>
//           );
//         })}
//       </Stepper>
//       <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 2 }}>
//         <Button disabled={activeStep === 0} onClick={handleBack} sx={{ mr: 1 }}>
//           Back
//         </Button>
//         <div>
//           {isStepOptional(activeStep) && (
//             <Button onClick={handleSkip} sx={{ mr: 1 }}>
//               Skip
//             </Button>
//           )}
//           <Button onClick={handleNext}>
//             {activeStep === steps.length - 1 ? 'Finish' : 'Next'}
//           </Button>
//         </div>
//       </Box>
//       {activeStep === steps.length && (
//         <Box sx={{ mt: 2 }}>
//           <Typography>All steps completed - you're finished</Typography>
//           <Button onClick={handleReset} sx={{ mt: 1 }}>
//             Reset
//           </Button>
//         </Box>
//       )}
//     </Box>
//   );
// }


// -----------------------------------

// import * as React from 'react';
// import { Stepper, Step, StepLabel, Button, Box, Typography } from '@mui/material';

// const steps = [
//   'Campaign Details',
//   'Prompt Files',
//   'Callback Configuration',
//   'Call Disposition Configuration',
//   'Inbound Group Configuration for Transfer',
//   'Minimum Hoper Level',
//   'Dial Method',
//   'Call Route on Hangup',
// ];

// const optionalSteps = [1, 3, 5]; // Indices of steps that are optional

// export default function HorizontalLinearStepper3() {
//   const [activeStep, setActiveStep] = React.useState(0);
//   const [completedSteps, setCompletedSteps] = React.useState(new Set());
//   const [skippedSteps, setSkippedSteps] = React.useState(new Set());

//   const isStepOptional = (step) => optionalSteps.includes(step);

//   const handleStepClick = (step) => () => {
//     setActiveStep(step);
//   };

//   const handleNext = () => {
//     let newActiveStep = activeStep + 1;
//     while (skippedSteps.has(newActiveStep)) {
//       newActiveStep++;
//     }
//     setActiveStep(newActiveStep);
//   };

//   const handleBack = () => {
//     setActiveStep((prevActiveStep) => prevActiveStep - 1);
//   };

//   const handleSkip = () => {
//     if (!isStepOptional(activeStep)) {
//       throw new Error("You can't skip a step that isn't optional.");
//     }
//     setActiveStep((prevActiveStep) => prevActiveStep + 1);
//     setSkippedSteps((prevSkippedSteps) => {
//       const newSkippedSteps = new Set(prevSkippedSteps);
//       newSkippedSteps.add(activeStep);
//       return newSkippedSteps;
//     });
//   };

//   const handleReset = () => {
//     setActiveStep(0);
//     setCompletedSteps(new Set());
//     setSkippedSteps(new Set());
//   };

//   const isStepCompleted = (step) => completedSteps.has(step);

//   const handleStepCompletion = (step) => () => {
//     const newCompletedSteps = new Set(completedSteps);
//     newCompletedSteps.add(step);
//     setCompletedSteps(newCompletedSteps);
//   };

//   return (
//     <Box sx={{ width: '100%' }}>
//       <Stepper activeStep={activeStep} alternativeLabel>
//         {steps.map((label, index) => {
//           const stepProps = {};
//           const labelProps = {};
//           const isCurrentStep = activeStep === index;

//           if (isStepOptional(index)) {
//             labelProps.optional = (
//               <Typography variant="caption" color="text.secondary">
//                 Optional
//               </Typography>
//             );
//           }

//           if (skippedSteps.has(index)) {
//             stepProps.completed = false;
//           } else {
//             stepProps.completed = isStepCompleted(index);
//           }

//           return (
//             <Step key={label} {...stepProps}>
//               <StepLabel
//                 onClick={handleStepClick(index)}
//                 completed={stepProps.completed}
//                 optional={labelProps.optional}
//                 sx={{
//                   cursor: 'pointer',
//                   '&:hover': {
//                     color: isCurrentStep ? 'inherit' : '#1976d2',
//                   },
//                 }}
//               >
//                 {label}
//               </StepLabel>
//             </Step>
//           );
//         })}
//       </Stepper>
//       <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 2 }}>
//         <Button disabled={activeStep === 0} onClick={handleBack} sx={{ mr: 1 }}>
//           Back
//         </Button>
//         <div>
//           {isStepOptional(activeStep) && (
//             <Button onClick={handleSkip} sx={{ mr: 1 }}>
//               Skip
//             </Button>
//           )}
//           <Button onClick={handleNext}>
//             {activeStep === steps.length - 1 ? 'Finish' : 'Next'}
//           </Button>
//         </div>
//       </Box>
//       {activeStep === steps.length && (
//         <Box sx={{ mt: 2 }}>
//           <Typography>All steps completed - you're finished</Typography>
//           <Button onClick={handleReset} sx={{ mt: 1 }}>
//             Reset
//           </Button>
//         </Box>
//       )}
//     </Box>
//   );
// }


//---------------------------------

// import * as React from 'react';
// import { Stepper, Step, StepLabel, Button, Box, Typography } from '@mui/material';

// const steps = [
//   'Campaign Details',
//   'Prompt Files',
//   'Callback Configuration',
//   'Call Disposition Configuration',
//   'Inbound Group Configuration for Transfer',
//   'Minimum Hoper Level',
//   'Dial Method',
//   'Call Route on Hangup',
// ];

// const optionalSteps = [1, 3, 5]; // Indices of steps that are optional

// export default function HorizontalLinearStepper3() {
//   const [activeStep, setActiveStep] = React.useState(0);
//   const [completedSteps, setCompletedSteps] = React.useState(new Set());
//   const [skippedSteps, setSkippedSteps] = React.useState(new Set());

//   const isStepOptional = (step) => optionalSteps.includes(step);

//   const handleStepClick = (step) => () => {
//     setActiveStep(step);
//   };

//   const handleNext = () => {
//     const newCompletedSteps = new Set(completedSteps);
//     newCompletedSteps.add(activeStep);
//     setCompletedSteps(newCompletedSteps);

//     let newActiveStep = activeStep + 1;
//     while (skippedSteps.has(newActiveStep)) {
//       newActiveStep++;
//     }
//     setActiveStep(newActiveStep);
//   };

//   const handleBack = () => {
//     setActiveStep((prevActiveStep) => prevActiveStep - 1);
//   };

//   const handleSkip = () => {
//     if (!isStepOptional(activeStep)) {
//       throw new Error("You can't skip a step that isn't optional.");
//     }
//     setActiveStep((prevActiveStep) => prevActiveStep + 1);
//     setSkippedSteps((prevSkippedSteps) => {
//       const newSkippedSteps = new Set(prevSkippedSteps);
//       newSkippedSteps.add(activeStep);
//       return newSkippedSteps;
//     });
//   };

//   const handleReset = () => {
//     setActiveStep(0);
//     setCompletedSteps(new Set());
//     setSkippedSteps(new Set());
//   };

//   const isStepCompleted = (step) => completedSteps.has(step);

//   return (
//     <Box sx={{ width: '100%' }}>
//       <Stepper activeStep={activeStep} alternativeLabel>
//         {steps.map((label, index) => {
//           const stepProps = {};
//           const labelProps = {};
//           const isCurrentStep = activeStep === index;

//           if (isStepOptional(index)) {
//             labelProps.optional = (
//               <Typography variant="caption" color="text.secondary">
//                 Optional
//               </Typography>
//             );
//           }

//           if (skippedSteps.has(index)) {
//             stepProps.completed = false;
//           } else {
//             stepProps.completed = isStepCompleted(index);
//           }

//           return (
//             <Step key={label} {...stepProps}>
//               <StepLabel
//                 onClick={handleStepClick(index)}
//                 completed={stepProps.completed}
//                 optional={labelProps.optional}
//                 sx={{
//                   cursor: 'pointer',
//                   '&:hover': {
//                     color: isCurrentStep ? 'inherit' : '#1976d2',
//                   },
//                 }}
//               >
//                 {label}
//               </StepLabel>
//             </Step>
//           );
//         })}
//       </Stepper>
//       <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 2 }}>
//         <Button disabled={activeStep === 0} onClick={handleBack} sx={{ mr: 1 }}>
//           Back
//         </Button>
//         <div>
//           {isStepOptional(activeStep) && (
//             <Button onClick={handleSkip} sx={{ mr: 1 }}>
//               Skip
//             </Button>
//           )}
//           <Button onClick={handleNext}>
//             {activeStep === steps.length - 1 ? 'Finish' : 'Next'}
//           </Button>
//         </div>
//       </Box>
//       {activeStep === steps.length && (
//         <Box sx={{ mt: 2 }}>
//           <Typography>All steps completed - you're finished</Typography>
//           <Button onClick={handleReset} sx={{ mt: 1 }}>
//             Reset
//           </Button>
//         </Box>
//       )}
//     </Box>
//   );
// }



//--------------------------------------

import React from 'react';
import { Stepper, Step, StepLabel, Button, Box, Typography } from '@mui/material';

const steps = [
  'Campaign Details',
  'Prompt Files',
  'Callback Configuration',
  'Call Disposition Configuration',
  'Inbound Group Configuration for Transfer',
  'Minimum Hoper Level',
  'Dial Method',
  'Call Route on Hangup',
];

const optionalSteps = [1, 3, 5];

export default function HorizontalLinearStepper3() {
  const [activeStep, setActiveStep] = React.useState(0);
  const [completedSteps, setCompletedSteps] = React.useState(new Set());
  const [skippedSteps, setSkippedSteps] = React.useState(new Set());

  const isStepOptional = (step) => optionalSteps.includes(step);

  const handleStepClick = (step) => () => {
    setActiveStep(step);
  };

  const handleNext = () => {
    if (isStepOptional(activeStep) && isStepCompleted(activeStep)) {
      // Check if the current step is not optional and is not completed
      return; // Do not allow to proceed to next step
    }

    const newCompletedSteps = new Set(completedSteps);
    newCompletedSteps.add(activeStep);
    setCompletedSteps(newCompletedSteps);

    let newActiveStep = activeStep + 1;
    while (skippedSteps.has(newActiveStep)) {
      newActiveStep++;
    }
    setActiveStep(newActiveStep);
  };

  const handleBack = () => {
    setActiveStep((prevActiveStep) => prevActiveStep - 1);
  };

  const handleSkip = () => {
    if (!isStepOptional(activeStep)) {
      throw new Error("You can't skip a step that isn't optional.");
    }
    setActiveStep((prevActiveStep) => prevActiveStep + 1);
    setSkippedSteps((prevSkippedSteps) => {
      const newSkippedSteps = new Set(prevSkippedSteps);
      newSkippedSteps.add(activeStep);
      return newSkippedSteps;
    });
  };

  const handleReset = () => {
    setActiveStep(0);
    setCompletedSteps(new Set());
    setSkippedSteps(new Set());
  };

  const isStepCompleted = (step) => completedSteps.has(step);

  return (
    <Box sx={{ width: '100%' }}>
      <Stepper activeStep={activeStep} alternativeLabel>
        {steps.map((label, index) => {
          const stepProps = {};
          const labelProps = {};
          const isCurrentStep = activeStep === index;

          if (isStepOptional(index)) {
            labelProps.optional = (
              <Typography variant="caption" color="text.secondary">
                Optional
              </Typography>
            );
          }

          if (skippedSteps.has(index)) {
            stepProps.completed = false;
          } else {
            stepProps.completed = isStepCompleted(index);
          }

          return (
            <Step key={label} {...stepProps}>
              <StepLabel
                onClick={handleStepClick(index)}
                completed={stepProps.completed}
                optional={labelProps.optional}
                sx={{
                  cursor: 'pointer',
                  '&:hover': {
                    color: isCurrentStep ? 'inherit' : '#1976d2',
                  },
                }}
              >
                {label}
              </StepLabel>
            </Step>
          );
        })}
      </Stepper>
      Current - step = {activeStep}
      <Box sx={{ display: 'flex', justifyContent: 'space-between', mt: 2 }}>
        <Button disabled={activeStep === 0} onClick={handleBack} sx={{ mr: 1 }}>
          Back
        </Button>
        <div>
          {isStepOptional(activeStep) && (
            <Button onClick={handleSkip} sx={{ mr: 1 }}>
              Skip
            </Button>
          )}
          <Button onClick={handleNext} disabled={isStepCompleted(activeStep) && isStepOptional(activeStep)}>
            {activeStep === steps.length - 1 ? 'Finish' : 'Next'}
          </Button>
        </div>
      </Box>
      {activeStep === steps.length && (
        <Box sx={{ mt: 2 }}>
          <Typography>All steps completed - you're finished</Typography>
          <Button onClick={handleReset} sx={{ mt: 1 }}>
            Reset
          </Button>
        </Box>
      )}
    </Box>
  );
}

