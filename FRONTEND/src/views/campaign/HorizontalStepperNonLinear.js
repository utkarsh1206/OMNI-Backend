import React, { useState } from 'react';
import { Stepper, Step, StepButton, Button, Typography, Box } from '@mui/material';
import Step1 from './createCampaign/Step1';
import Step2 from './createCampaign/Step2';
import { CButton } from '@coreui/react';

// Array of steps in the campaign creation process
const steps = [
    'Campaign Details',
    'Prompt Files',
    'Callback Configuration',
    'Call Disposition Configuration',
    'Inbound Group Configuration for Transfer',
    'Minimum Hoper Level',
    'Dial Method',
    'Call Route on Hangup'
];

// Array of step indices that are optional (zero-based index)
const optionalSteps = [1, 3, 4];

export default function HorizontalNonLinearStepper() {
    const [activeStep, setActiveStep] = useState(0);
    const [completed, setCompleted] = useState({});
    const [formData, setFormData] = useState({
        campID: '',
        campName: '',
        msc_on_hld: '',
        welcomeMsg: '',
        // Initialize states for other steps if needed
    });

    // Calculate total number of steps
    const totalSteps = () => {
        return steps.length;
    };

    // Calculate number of completed steps
    const completedSteps = () => {
        return Object.keys(completed).length;
    };

    // Check if a step is optional
    const isStepOptional = (step) => {
        return optionalSteps.includes(step);
    };

    // Check if current step is the last step
    const isLastStep = () => {
        return activeStep === totalSteps() - 1;
    };

    // Check if all steps are completed
    const allStepsCompleted = () => {
        return completedSteps() === totalSteps();
    };

    // Handler for moving to the next step
    const handleNext = () => {
        const newActiveStep =
            isLastStep() && !allStepsCompleted()
                ? // It's the last step, but not all steps have been completed,
                // find the first step that has been completed
                steps.findIndex((step, i) => !(i in completed))
                : activeStep + 1;
        setActiveStep(newActiveStep);
    };

    // Handler for moving to the previous step
    const handleBack = () => {
        setActiveStep((prevActiveStep) => prevActiveStep - 1);
    };

    // Handler for directly selecting a step
    const handleStep = (step) => () => {
        setActiveStep(step);
    };

    // Handler for marking a step as completed and moving to the next step
    const handleComplete = () => {
        const newCompleted = completed;
        newCompleted[activeStep] = true;
        setCompleted(newCompleted);
        handleNext();
    };

    // Handler for finishing the campaign creation process
    const handleFinish = () => {
        console.log('All data submitted:', formData);
        // Perform submission logic here, e.g., send data to an API
    };

    // Handler for resetting the stepper to the first step
    const handleReset = () => {
        setActiveStep(0);
        setCompleted({});
    };

    return (
        <Box sx={{ width: '100%' }}>
            {/* Stepper component for displaying the steps */}
            <Stepper alternativeLabel nonLinear activeStep={activeStep}>
                {steps.map((label, index) => {
                    const labelProps = {};
                    if (isStepOptional(index)) {
                        labelProps.optional = (
                            <Typography variant="caption" color="text.secondary">
                                Optional
                            </Typography>
                        );
                    }
                    return (
                        <Step key={label} completed={completed[index]}>
                            <StepButton {...labelProps} color="inherit" onClick={handleStep(index)}>
                                {label}
                            </StepButton>
                        </Step>
                    );
                })}
            </Stepper>

            <div>
                {/* Display different step components based on activeStep */}
                {allStepsCompleted() ? (
                    // All steps completed message
                    <React.Fragment>
                        <Typography sx={{ mt: 2, mb: 1 }}>
                            All steps completed - you&apos;re finished
                        </Typography>
                        <Box sx={{ display: 'flex', flexDirection: 'row', pt: 2 }}>
                            <Box sx={{ flex: '1 1 auto' }} />
                            <CButton color="primary" variant='outline' onClick={handleReset}>Reset</CButton>
                        </Box>
                    </React.Fragment>
                ) : (
                    // Render step components based on activeStep
                    <React.Fragment>
                        {(() => {
                            switch (activeStep) {
                                case 0:
                                    // Render Step1 component
                                    // return <Step1 formData={formData.step1} setFormData={(data) => setFormData(prev => ({ ...prev, step1: data }))} />
                                    return <Step1 formData={formData} setFormData={setFormData} />;
                                case 1:
                                    // Render Step2 component
                                    return <Step2 formData={formData} setFormData={setFormData} />;
                                case 2:
                                    // Code for step 
                                    return <h6>Step 3</h6>
                                case 3:
                                    // Code for step 3
                                    return <h6>Step 4</h6>
                                case 4:
                                    // Code for step 4
                                    return <h6>Step 5</h6>
                                case 5:
                                    // Code for step 5
                                    return <h6>Step 6</h6>
                                case 6:
                                    // Code for step 6
                                    return <h6>Step 7</h6>
                                case 7:
                                    // Code for step 7
                                    return <h6>Step 8</h6>
                                default:
                                    // Code for any other step
                                    return null;
                            }
                        })()}
                        {/* Navigation buttons */}
                        <Box sx={{ display: 'flex', flexDirection: 'row', pt: 2 }}>
                            <CButton
                                // color="inherit"
                                color="secondary"
                                variant='ghost'
                                disabled={activeStep === 0}
                                onClick={handleBack}
                                sx={{ mr: 1 }}
                            >
                                Back
                            </CButton>
                            <Box sx={{ flex: '1 1 auto' }} />
                            <CButton color="primary" variant='outline' onClick={handleNext} sx={{ mr: 1 }}>
                                Next
                            </CButton>
                            {/* Conditional rendering based on step completion */}
                            {activeStep !== steps.length &&
                                (completed[activeStep] ? (
                                    <Typography variant="caption" sx={{ display: 'inline-block' }}>
                                        Step {activeStep + 1} already completed
                                    </Typography>
                                ) : (
                                    // <Button onClick={handleComplete}>
                                    <CButton color="primary" variant='outline' onClick={() => {
                                        if (completedSteps() === totalSteps() - 1) {
                                            handleFinish();
                                            handleComplete();
                                        } else {
                                            handleComplete();
                                        }
                                    }}>
                                        {completedSteps() === totalSteps() - 1
                                            ? 'Finish'
                                            : 'Complete Step'}
                                    </CButton>
                                ))}
                        </Box>
                    </React.Fragment>
                )}
            </div>
        </Box>
    );
}
