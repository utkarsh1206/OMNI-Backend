import React, { useState } from 'react';
import { Stepper, Step, StepLabel, Button, Typography, Box } from '@mui/material';
import Step1 from './createCampaign/Step1';
import Step2 from './createCampaign/Step2';

// Array of steps in the campaign creation process
const steps = ['Campaign Details', 'Prompt Files', 'Callback Configuration', 'Call Disposition Configuration', 'Inbound Group Configuration for Transfer', 'Minimum Hoper Level', 'Dial Method', 'Call Route on Hangup'];

// Array of step indices that are optional (zero-based index)
const optionalSteps = [1, 3, 5];

const HorizontalStepper = () => {
    const [activeStep, setActiveStep] = useState(0);
    const [skipped, setSkipped] = useState(new Set());
    const [formData, setFormData] = useState({
        campID: '',
        campName: '',
        msc_on_hld: '',
        welcomeMsg: '',
        // Initialize states for other steps if needed
    });

    const isStepOptional = (step, valuesToCheck) => {
        return valuesToCheck.includes(step);
    };      // added

    const isStepSkipped = (step) => {
        return skipped.has(step);
    };      // added

    const handleNext = () => {
        let newSkipped = skipped;
        if (isStepSkipped(activeStep)) {
            newSkipped = new Set(newSkipped.values());
            newSkipped.delete(activeStep);
        }

        setActiveStep((prevActiveStep) => prevActiveStep + 1);
        setSkipped(newSkipped);
    };

    const handleBack = () => {
        setActiveStep(prevActiveStep => prevActiveStep - 1);
    };      // added

    const handleSkip = () => {
        if (!isStepOptional(activeStep, optionalSteps)) {
            // You probably want to guard against something like this,
            // it should never occur unless someone's actively trying to break something.
            throw new Error("You can't skip a step that isn't optional.");
        }

        setActiveStep((prevActiveStep) => prevActiveStep + 1);
        setSkipped((prevSkipped) => {
            const newSkipped = new Set(prevSkipped.values());
            newSkipped.add(activeStep);
            return newSkipped;
        });
    };

    const handleReset = () => {
        setActiveStep(0);
        setFormData({
            campID: '',
            campName: '',
            msc_on_hld: '',
            welcomeMsg: '',
            // Reset states for other steps if needed
        });
    };      // added

    const handleFinish = () => {
        console.log('All data submitted:', formData);
        // Perform submission logic here, e.g., send data to an API
    };      // added

    const handleStepClick = (index) => {
        setActiveStep(index);
    };

    return (
        <>
            <Stepper alternativeLabel activeStep={activeStep}>
                {steps.map((label, index) => {
                    const stepProps = {};
                    const labelProps = {};
                    if (isStepOptional(index, optionalSteps)) {
                        labelProps.optional = (
                            <small>Optional</small>
                        );
                    }
                    if (isStepSkipped(index)) {
                        stepProps.completed = false;
                    }
                    return (
                        <Step key={label} {...stepProps}>
                            <StepLabel {...labelProps} onClick={() => handleStepClick(index)}>{label}</StepLabel>
                        </Step>
                    );
                })}
            </Stepper>

            {activeStep === steps.length ? (
                <React.Fragment>
                    <Typography sx={{ mt: 2, mb: 1 }}>All steps completed - you&apos;re finished</Typography>
                    <Box sx={{ display: 'flex', flexDirection: 'row', pt: 2 }}>
                        <Box sx={{ flex: '1 1 auto' }} />
                        <Button onClick={handleReset}>Reset</Button>
                    </Box>
                </React.Fragment>
            ) : (
                <React.Fragment>
                    {(() => {
                        switch (activeStep) {
                            case 0:
                                // return <Step1 formData={formData.step1} setFormData={(data) => setFormData(prev => ({ ...prev, step1: data }))} />
                                return <Step1 formData={formData} setFormData={setFormData} />;
                            case 1:
                                // return <Step2 formData={formData.step2} setFormData={(data) => setFormData(prev => ({ ...prev, step2: data }))} />
                                return <Step2 formData={formData} setFormData={setFormData} />;
                            case 2:
                                // Code for step 
                                break;
                            case 3:
                                // Code for step 3
                                break;
                            case 4:
                                // Code for step 4
                                break;
                            case 5:
                                // Code for step 5
                                break;
                            case 6:
                                // Code for step 6
                                break;
                            case 7:
                                // Code for step 7
                                break;
                            default:
                                // Code for any other step
                                return null;
                        }
                    })()}


                    <Box sx={{ display: 'flex', flexDirection: 'row', pt: 2 }}>
                        <Button color="inherit" disabled={activeStep === 0} onClick={handleBack} sx={{ mr: 1 }}>
                            Back
                        </Button>
                        <Box sx={{ flex: '1 1 auto' }} />
                        {isStepOptional(activeStep, optionalSteps) && (
                            <Button color="inherit" onClick={handleSkip} sx={{ mr: 1 }}>
                                Skip
                            </Button>
                        )}

                        {/* <Button onClick={handleNext}>
                            {activeStep === steps.length - 1 ? 'Finish' : 'Next'}
                        </Button> */}
                        <Button onClick={activeStep === steps.length - 1 ? handleFinish : handleNext}>
                            {activeStep === steps.length - 1 ? 'Finish' : 'Next'}
                        </Button>
                    </Box>
                </React.Fragment>
            )}
        </>
    );
};

export default HorizontalStepper;
