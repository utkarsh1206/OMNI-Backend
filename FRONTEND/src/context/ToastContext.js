import React, { createContext, useContext, useState } from 'react';
import { CToast, CToaster, CToastBody, CToastClose } from '@coreui/react';

// Create a Toast Context
const ToastContext = createContext();

// Create a Toast Provider to provide toast functions to the rest of the app
export const ToastProvider = ({ children }) => {
    const [toasts, setToasts] = useState([]);

    const addToast = (toast) => {
        setToasts([...toasts, toast]);
    };

    const removeToast = (id) => {
        setToasts(toasts.filter(toast => toast.id !== id));
    };

    return (
        <ToastContext.Provider value={{ addToast }}>
            {children}
            {/* Render CoreUI Toast here */}
            <CToaster placement="top-end" className="p-3">
                {toasts.map((toast) => (
                    <CToast
                        key={toast.id}
                        visible={true}
                        color={toast.type}
                        onClose={() => removeToast(toast.id)}
                        style={{ '--cui-toast-color': '#f2f3f4' }}
                    >
                        <div className="d-flex">
                            <CToastBody>{toast.message}</CToastBody>
                            <CToastClose className="me-2 m-auto" dark={true} />
                        </div>
                    </CToast>
                ))}
            </CToaster>
        </ToastContext.Provider>
    );
};

// Custom Hook for using toast in any component
export const useToast = () => {
    const { addToast } = useContext(ToastContext);

    const showToast = (message, type = 'success') => {
        const id = new Date().getTime(); // Generate a unique ID based on the timestamp
        addToast({ id, message, type });
    };

    return showToast;
};