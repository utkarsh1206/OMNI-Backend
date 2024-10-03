// SignupForm.js
import React, { useState } from 'react';
import {
    CInputGroup,
    CFormLabel,
    CFormInput,
    CFormFeedback,
    CButton,
    CRow,
    CCol
} from '@coreui/react';

const ValidationForm = () => {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [emailError, setEmailError] = useState('');
    const [passwordError, setPasswordError] = useState('');

    const validateEmail = () => {
        if (!email) {
            setEmailError('Email is required');
            return false;
        } else if (!/\S+@\S+\.\S+/.test(email)) {
            setEmailError('Invalid email address');
            return false;
        } else {
            setEmailError('');
            return true;
        }
    };

    const validatePassword = () => {
        if (!password) {
            setPasswordError('Password is required');
            return false;
        } else if (password.length < 5) {
            setPasswordError('Password must be at least 5 characters long');
            return false;
        } else if (!/(?=.*[A-Z])(?=.*[!@#$&*])(?=.*[0-9])/.test(password)) {
            setPasswordError('Password must contain at least one uppercase letter, one special character, and one number');
            return false;
        } else {
            setPasswordError('');
            return true;
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();

        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();

        if (isEmailValid && isPasswordValid) {
            // Handle form submission logic here (e.g., API call)
            alert(`Email: ${email}\nPassword: ${password}`);
        }
    };

    return (
        <form onSubmit={handleSubmit}>
            <CRow className='mb-2'>
                <CCol sm={2}>
                    <CFormLabel>Email</CFormLabel>
                </CCol>
                <CCol>
                    <CFormInput
                        type="email"
                        placeholder="Enter your email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        invalid={emailError !== ''}
                    />
                    {emailError && <CFormFeedback invalid>{emailError}</CFormFeedback>}
                </CCol>
            </CRow>
            <CRow className='mb-2'>
                <CCol sm={2}>
                    <CFormLabel>Password</CFormLabel>
                </CCol>
                <CCol>
                    <CFormInput
                        type="password"
                        placeholder="Enter your password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        invalid={passwordError !== ''}
                    />
                    {passwordError && <CFormFeedback invalid>{passwordError}</CFormFeedback>}
                </CCol>
            </CRow>

            <CButton type="submit" color="primary">Submit</CButton>
        </form>
    );
};

export default ValidationForm;
